import type { Config, Context } from "@netlify/functions";
import { desc, eq } from "drizzle-orm";
import { db } from "../../db/index.js";
import { events, orders, ticketTypes } from "../../db/schema.js";
import { checkLogin, clearCookie, isAdmin, sessionCookie } from "../../lib/auth.js";

const unauthorized = () => Response.json({ error: "Unauthorized" }, { status: 401 });

async function login(req: Request) {
  const { username = "", password = "" } = await req.json().catch(() => ({}));
  if (!checkLogin(String(username).trim(), String(password).trim())) {
    return Response.json({ error: "Username atau password salah." }, { status: 401 });
  }
  return Response.json({ ok: true }, { headers: { "Set-Cookie": sessionCookie() } });
}

async function createEvent(req: Request) {
  const body = await req.json().catch(() => ({}));
  const title = String(body.title ?? "").trim();
  const location = String(body.location ?? "").trim();
  const eventDate = new Date(body.eventDate);
  if (!title || !location || isNaN(eventDate.getTime())) {
    return Response.json({ error: "Data event tidak lengkap." }, { status: 400 });
  }

  const tickets = (Array.isArray(body.tickets) ? body.tickets : [])
    .map((t: any) => ({
      name: String(t.name ?? "").trim(),
      price: Math.max(0, Math.floor(Number(t.price) || 0)),
      quota: Math.max(0, Math.floor(Number(t.quota) || 0)),
    }))
    .filter((t: { name: string }) => t.name);

  const [event] = await db
    .insert(events)
    .values({
      title,
      description: String(body.description ?? "").trim(),
      eventDate,
      location,
      bannerImage: String(body.bannerImage ?? "").trim() || null,
    })
    .returning();

  if (tickets.length) {
    await db.insert(ticketTypes).values(tickets.map((t: any) => ({ ...t, eventId: event.id })));
  }
  return Response.json(event, { status: 201 });
}

export default async (req: Request, context: Context) => {
  const action = context.params.action;
  const id = Number(context.params.id);

  if (action === "login" && req.method === "POST") return login(req);
  if (action === "logout" && req.method === "POST") {
    return Response.json({ ok: true }, { headers: { "Set-Cookie": clearCookie() } });
  }

  if (!isAdmin(req)) return unauthorized();

  if (action === "me") return Response.json({ ok: true });

  if (action === "events") {
    if (req.method === "GET") {
      return Response.json(await db.select().from(events).orderBy(desc(events.eventDate)));
    }
    if (req.method === "POST") return createEvent(req);
    if (req.method === "DELETE" && Number.isInteger(id)) {
      // Ticket types and orders are removed via ON DELETE CASCADE
      await db.delete(events).where(eq(events.id, id));
      return Response.json({ ok: true });
    }
  }

  if (action === "orders" && req.method === "GET") {
    const list = await db
      .select({
        id: orders.id,
        bookingCode: orders.bookingCode,
        buyerName: orders.buyerName,
        buyerEmail: orders.buyerEmail,
        quantity: orders.quantity,
        totalPrice: orders.totalPrice,
        createdAt: orders.createdAt,
        ticketName: ticketTypes.name,
        eventTitle: events.title,
      })
      .from(orders)
      .innerJoin(ticketTypes, eq(orders.ticketTypeId, ticketTypes.id))
      .innerJoin(events, eq(ticketTypes.eventId, events.id))
      .orderBy(desc(orders.createdAt));
    return Response.json(list);
  }

  return new Response("Not found", { status: 404 });
};

export const config: Config = {
  path: ["/api/admin/:action", "/api/admin/:action/:id"],
};
