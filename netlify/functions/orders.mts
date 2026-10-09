import type { Config, Context } from "@netlify/functions";
import { and, eq, sql } from "drizzle-orm";
import { randomBytes } from "node:crypto";
import { db } from "../../db/index.js";
import { events, orders, ticketTypes } from "../../db/schema.js";

async function createOrder(req: Request) {
  const body = await req.json().catch(() => ({}));
  const ticketTypeId = Number(body.ticketTypeId);
  const buyerName = String(body.buyerName ?? "").trim();
  const buyerEmail = String(body.buyerEmail ?? "").trim();
  const quantity = Math.floor(Number(body.quantity));

  if (!Number.isInteger(ticketTypeId) || !buyerName || !buyerEmail || !(quantity >= 1)) {
    return Response.json({ error: "Data tidak lengkap." }, { status: 400 });
  }

  // Reserve seats atomically so two buyers can't oversell the quota
  const [ticket] = await db
    .update(ticketTypes)
    .set({ soldCount: sql`${ticketTypes.soldCount} + ${quantity}` })
    .where(
      and(
        eq(ticketTypes.id, ticketTypeId),
        sql`${ticketTypes.quota} - ${ticketTypes.soldCount} >= ${quantity}`,
      ),
    )
    .returning();

  if (!ticket) {
    const [existing] = await db.select().from(ticketTypes).where(eq(ticketTypes.id, ticketTypeId));
    if (!existing) return Response.json({ error: "Tiket tidak ditemukan." }, { status: 404 });
    const sisa = existing.quota - existing.soldCount;
    return Response.json(
      { error: `Maaf, kuota tidak mencukupi. Sisa tiket: ${sisa}` },
      { status: 409 },
    );
  }

  const bookingCode = "TIX-" + randomBytes(3).toString("hex").toUpperCase();
  try {
    await db.insert(orders).values({
      ticketTypeId,
      buyerName,
      buyerEmail,
      quantity,
      totalPrice: ticket.price * quantity,
      bookingCode,
    });
  } catch {
    await db
      .update(ticketTypes)
      .set({ soldCount: sql`${ticketTypes.soldCount} - ${quantity}` })
      .where(eq(ticketTypes.id, ticketTypeId));
    return Response.json({ error: "Terjadi kesalahan, silakan coba lagi." }, { status: 500 });
  }

  return Response.json({ bookingCode }, { status: 201 });
}

async function getOrder(code: string) {
  const [order] = await db
    .select({
      bookingCode: orders.bookingCode,
      buyerName: orders.buyerName,
      buyerEmail: orders.buyerEmail,
      quantity: orders.quantity,
      totalPrice: orders.totalPrice,
      ticketName: ticketTypes.name,
      eventTitle: events.title,
      eventDate: events.eventDate,
      location: events.location,
    })
    .from(orders)
    .innerJoin(ticketTypes, eq(orders.ticketTypeId, ticketTypes.id))
    .innerJoin(events, eq(ticketTypes.eventId, events.id))
    .where(eq(orders.bookingCode, code));

  if (!order) {
    return Response.json({ error: "Data pesanan tidak ditemukan." }, { status: 404 });
  }
  return Response.json(order);
}

export default async (req: Request, context: Context) => {
  if (req.method === "POST" && !context.params.code) return createOrder(req);
  if (req.method === "GET" && context.params.code) return getOrder(context.params.code);
  return new Response("Method not allowed", { status: 405 });
};

export const config: Config = {
  path: ["/api/orders", "/api/orders/:code"],
};
