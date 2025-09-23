const API_BASE = "../api"; // adjust if your /api is elsewhere
const API_SEARCH = `${API_BASE}/search.php`;
const API_LOG = `${API_BASE}/log_click.php`;

const $input = document.getElementById('agency');
const $box = document.getElementById('suggestions');
let items = [];
let active = -1;
let timer;

injectStyle(`
  #suggestions{ position:relative; margin-top:6px; }
  #suggestions .dropdown{ position:absolute; left:0; right:0; background:#fff; border:1px solid #e6e6e6; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,.07); overflow:hidden; z-index:9999; }
  #suggestions .item{ padding:10px 12px; cursor:pointer; }
  #suggestions .item:hover, #suggestions .item.active{ background:#f5f7ff; }
  #suggestions .title{ font-weight:600; }
  #suggestions .sub{ color:#666; font-size:12px; margin-top:2px; }
  #suggestions mark{ background:#fff3b0; }
`);

$input.addEventListener('input', () => {
  const q = $input.value.trim();
  clearTimeout(timer);
  if (!q){ hide(); return; }
  timer = setTimeout(async () => {
    const res = await fetch(`${API_SEARCH}?q=${encodeURIComponent(q)}&limit=8`).then(r=>r.json()).catch(()=>({items:[]}));
    items = res.items || [];
    active = -1;
    render();
  }, 180);
});

$input.addEventListener('keydown', (e) => {
  if ($box.style.display === 'none') return;
  if (e.key === 'ArrowDown'){ e.preventDefault(); move(1); }
  else if (e.key === 'ArrowUp'){ e.preventDefault(); move(-1); }
  else if (e.key === 'Enter'){ e.preventDefault(); if (active>=0) select(items[active]); }
  else if (e.key === 'Escape'){ hide(); }
});

document.addEventListener('click', (e) => {
  if (!($box.contains(e.target) || e.target === $input)) hide();
});

function render(){
  if (!items.length){ hide(); return; }
  $box.innerHTML = `
    <div class="dropdown">
      ${items.map((it,i)=> `
        <div class="item ${i===active?'active':''}" data-i="${i}">
          <div class="title">${it.display?.title ?? ''}</div>
          <div class="sub">${it.display?.subtitle ?? ''}</div>
          <div class="sub">${it.display?.snippet ?? ''}</div>
        </div>
      `).join('')}
    </div>`;
  $box.style.display = 'block';
  [...$box.querySelectorAll('.item')].forEach(el=>{
    el.addEventListener('mousemove', ()=>{ active = parseInt(el.dataset.i,10); render(); });
    el.addEventListener('click', ()=> select(items[parseInt(el.dataset.i,10)]));
  });
}

function hide(){ $box.style.display = 'none'; active = -1; }
function move(step){ if (!items.length) return; active = (active + step + items.length) % items.length; render(); }

async function select(it){
  hide();
  if (!it) return;
  $input.value = it.display?.title ?? '';
  try {
    await fetch(API_LOG, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({ service_id: it.service_id, q: $input.value }) });
  } catch(e){}

  const detail = {
    serviceId: it.service_id,
    title: it.display?.title,
    departmentTH: it.department_name_th,
    departmentEN: it.department_name_en,
    building: it.building_name,
    floor: it.floor,
    room: it.room_number,
    phone: it.phone,
    email: it.email,
    lat: toNum(it.lat),
    lng: toNum(it.lng)
  };
  document.dispatchEvent(new CustomEvent('search:selected', { detail }));
  if (typeof window.showTargetOnMap === 'function' && detail.lat && detail.lng){
    window.showTargetOnMap({ lat: detail.lat, lng: detail.lng, title: detail.title });
  }
}

function toNum(v){ const n = Number(v); return Number.isFinite(n) ? n : null; }
function injectStyle(s){ const tag = document.createElement('style'); tag.textContent = s; document.head.appendChild(tag); }