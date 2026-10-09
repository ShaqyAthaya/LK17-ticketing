import { pgTable, serial, text, timestamp, integer } from "drizzle-orm/pg-core";

export const events = pgTable("events", {
  id: serial().primaryKey(),
  title: text().notNull(),
  description: text().notNull().default(""),
  eventDate: timestamp("event_date").notNull(),
  location: text().notNull(),
  bannerImage: text("banner_image"),
  status: text().notNull().default("active"),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

export const ticketTypes = pgTable("ticket_types", {
  id: serial().primaryKey(),
  eventId: integer("event_id")
    .notNull()
    .references(() => events.id, { onDelete: "cascade" }),
  name: text().notNull(),
  price: integer().notNull(),
  quota: integer().notNull(),
  soldCount: integer("sold_count").notNull().default(0),
});

export const orders = pgTable("orders", {
  id: serial().primaryKey(),
  ticketTypeId: integer("ticket_type_id")
    .notNull()
    .references(() => ticketTypes.id, { onDelete: "cascade" }),
  buyerName: text("buyer_name").notNull(),
  buyerEmail: text("buyer_email").notNull(),
  quantity: integer().notNull(),
  totalPrice: integer("total_price").notNull(),
  bookingCode: text("booking_code").notNull().unique(),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});
