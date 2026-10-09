CREATE TABLE "events" (
	"id" serial PRIMARY KEY,
	"title" text NOT NULL,
	"description" text DEFAULT '' NOT NULL,
	"event_date" timestamp NOT NULL,
	"location" text NOT NULL,
	"banner_image" text,
	"status" text DEFAULT 'active' NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "orders" (
	"id" serial PRIMARY KEY,
	"ticket_type_id" integer NOT NULL,
	"buyer_name" text NOT NULL,
	"buyer_email" text NOT NULL,
	"quantity" integer NOT NULL,
	"total_price" integer NOT NULL,
	"booking_code" text NOT NULL UNIQUE,
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "ticket_types" (
	"id" serial PRIMARY KEY,
	"event_id" integer NOT NULL,
	"name" text NOT NULL,
	"price" integer NOT NULL,
	"quota" integer NOT NULL,
	"sold_count" integer DEFAULT 0 NOT NULL
);
--> statement-breakpoint
ALTER TABLE "orders" ADD CONSTRAINT "orders_ticket_type_id_ticket_types_id_fkey" FOREIGN KEY ("ticket_type_id") REFERENCES "ticket_types"("id") ON DELETE CASCADE;--> statement-breakpoint
ALTER TABLE "ticket_types" ADD CONSTRAINT "ticket_types_event_id_events_id_fkey" FOREIGN KEY ("event_id") REFERENCES "events"("id") ON DELETE CASCADE;