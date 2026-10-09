import { createHmac, timingSafeEqual } from "node:crypto";

const COOKIE = "admin_session";
const MAX_AGE = 60 * 60 * 8; // 8 hours

function credentials() {
  const username = process.env.ADMIN_USERNAME;
  const password = process.env.ADMIN_PASSWORD;
  if (!username || !password) return null;
  return { username, password };
}

function sign(value: string, secret: string) {
  return createHmac("sha256", secret).update(value).digest("hex");
}

function safeEqual(a: string, b: string) {
  const ba = Buffer.from(a);
  const bb = Buffer.from(b);
  return ba.length === bb.length && timingSafeEqual(ba, bb);
}

export function checkLogin(username: string, password: string) {
  const creds = credentials();
  if (!creds) return false;
  return safeEqual(username, creds.username) && safeEqual(password, creds.password);
}

export function sessionCookie() {
  const creds = credentials()!;
  const expires = Math.floor(Date.now() / 1000) + MAX_AGE;
  const payload = `${creds.username}.${expires}`;
  const token = `${payload}.${sign(payload, creds.password)}`;
  return `${COOKIE}=${token}; Path=/; HttpOnly; Secure; SameSite=Lax; Max-Age=${MAX_AGE}`;
}

export function clearCookie() {
  return `${COOKIE}=; Path=/; HttpOnly; Secure; SameSite=Lax; Max-Age=0`;
}

export function isAdmin(req: Request) {
  const creds = credentials();
  if (!creds) return false;
  const cookie = req.headers.get("cookie") ?? "";
  const match = cookie.match(new RegExp(`(?:^|;\\s*)${COOKIE}=([^;]+)`));
  if (!match) return false;
  const parts = match[1].split(".");
  const sig = parts.pop()!;
  const payload = parts.join(".");
  const expires = Number(parts.pop());
  if (!expires || expires < Date.now() / 1000) return false;
  return safeEqual(sig, sign(payload, creds.password));
}
