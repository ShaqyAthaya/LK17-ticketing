import type { Config, Context } from "@netlify/functions";
import { eq } from "drizzle-orm";
import { db } from "../../db/index.js";
import { events, ticketTypes } from "../../db/schema.js";

export default async (req: Request, context: Context) => {
  const id = Number(context.params.id);
  if (!Number.isInteger(id)) {
    return Response.json({ error: "Tiket tidak ditemukan." }, { status: 404 });
  }

  const [ticket] = await db
    .select({
      id: ticketTypes.id,
      name: ticketTypes.name,
      price: ticketTypes.price,
      quota: ticketTypes.quota,
      soldCount: ticketTypes.soldCount,
      eventTitle: events.title,
      eventDate: events.eventDate,
      location: events.location,
    })
    .from(ticketTypes)
    .innerJoin(events, eq(ticketTypes.eventId, events.id))
    .where(eq(ticketTypes.id, id));

  if (!ticket) {
    return Response.json({ error: "Tiket tidak ditemukan." }, { status: 404 });
  }
  return Response.json(ticket);
};

export const config: Config = {
  path: "/api/tickets/:id",
  method: "GET",
};
