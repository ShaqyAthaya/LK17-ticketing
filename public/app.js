const BULAN = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

function esc(value) {
  const div = document.createElement("div");
  div.textContent = value ?? "";
  return div.innerHTML;
}

function rupiah(n) {
  return "Rp " + Number(n).toLocaleString("id-ID", { maximumFractionDigits: 0 });
}

function tanggal(value, withTime = true) {
  const d = new Date(value);
  const pad = (x) => String(x).padStart(2, "0");
  const base = `${pad(d.getDate())} ${BULAN[d.getMonth()]} ${d.getFullYear()}`;
  return withTime ? `${base}, ${pad(d.getHours())}:${pad(d.getMinutes())}` : base;
}

async function api(url, options = {}) {
  const res = await fetch(url, {
    ...options,
    headers: { "Content-Type": "application/json", ...(options.headers || {}) },
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data.error || "Terjadi kesalahan, silakan coba lagi.");
    err.status = res.status;
    throw err;
  }
  return data;
}

function showError(el, message) {
  el.innerHTML = `<div class="alert alert-danger">${esc(message)}</div>`;
}
