import type { Config, Context } from "@netlify/functions";
import { and, asc, eq } from "drizzle-orm";
import { db } from "../../db/index.js";
import { events, ticketTypes } from "../../db/schema.js";

export default async (req: Request, context: Context) => {
  const id = Number(context.params.id);

  if (!context.params.id) {
    const list = await db
      .select()
      .from(events)
      .where(eq(events.status, "active"))
      .orderBy(asc(events.eventDate));
    return Response.json(list);
  }

  if (!Number.isInteger(id)) {
    return Response.json({ error: "Event tidak ditemukan." }, { status: 404 });
  }

  const [event] = await db
    .select()
    .from(events)
    .where(and(eq(events.id, id), eq(events.status, "active")));
  if (!event) {
    return Response.json({ error: "Event tidak ditemukan." }, { status: 404 });
  }

  const tickets = await db
    .select()
    .from(ticketTypes)
    .where(eq(ticketTypes.eventId, id))
    .orderBy(asc(ticketTypes.id));

  return Response.json({ ...event, tickets });
};

export const config: Config = {
  path: ["/api/events", "/api/events/:id"],
  method: "GET",
};
