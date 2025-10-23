<?php
// pos.php
include "db.php";
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>POS - Point of Sale</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { direction: rtl; font-family: Arial, sans-serif; background:#f7f7f9; }
        .scan-box { max-width:700px; margin:10px auto; }
        .cart-table td, .cart-table th { vertical-align: middle; }
        .big-input { font-size: 24px; padding: 14px; text-align: center; }
        .summary { font-size: 20px; font-weight:700; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="document.getElementById('barcode').focus();">

<div class="container py-3">

    <div class="row no-print">
        <div class="col-md-6 scan-box">
            <div class="card shadow-sm p-3">
                <h4 class="mb-3">امسح الباركود</h4>
                <form id="scanForm" onsubmit="return false;">
                    <input id="barcode" name="barcode" class="form-control big-input" autocomplete="off" placeholder="Scan barcode here..." />
                </form>

                <div id="scanResult" class="mt-3"></div>

                <div class="mt-3">
                    <button id="clearCartBtn" class="btn btn-secondary">مسح الفاتورة</button>
                    <button id="printBtn" class="btn btn-success">طباعة الفاتورة</button>
                </div>
            </div>
        </div>

        <div class="col-md-6 scan-box">
            <div class="card shadow-sm p-3">
                <h4 class="mb-3">معلومات سريعة</h4>
                <p>ركز المؤشر على خانة المسح — ماسح USB (HID) يكتب الأرقام مباشرة.</p>
                <p>إذا لم يكتب شيء: جرّب المفكرة Notepad للتأكد من وضع الماسح.</p>
                <div class="mt-3">
                    <div><strong>عدد الأصناف:</strong> <span id="itemsCount">0</span></div>
                    <div class="mt-2 summary"><strong>الإجمالي: $<span id="totalPrice">0.00</span></strong></div>
                </div>
            </div>
        </div>
        <form action="dashboard.php">
            <button type="submit" name="save" class="btn btn-primary w-10">return to dashboard page</button>
        </form>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>قائمة الفاتورة</h5>
                    <div class="table-responsive">
                        <table id="cartTable" class="table table-bordered cart-table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>اسم المنتج</th>
                                    <th>باركود</th>
                                    <th>السعر</th>
                                    <th>الكمية</th>
                                    <th>المجموع</th>
                                    <th class="no-print">إجراء</th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>


const barcodeInput = document.getElementById('barcode');
const cartBody = document.getElementById('cartBody');
const itemsCountEl = document.getElementById('itemsCount');
const totalPriceEl = document.getElementById('totalPrice');
const scanResult = document.getElementById('scanResult');

let cart = {}; 

function beep(status) {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const o = ctx.createOscillator();
    const g = ctx.createGain();
    o.connect(g);
    g.connect(ctx.destination);
    if (status === 'ok') {
        o.frequency.value = 900;
    } else {
        o.frequency.value = 300;
    }
    o.start();
    g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.12);
    setTimeout(() => { try{ o.stop(); ctx.close(); } catch(e){} }, 150);
}

function updateCartUI(){
    cartBody.innerHTML = '';
    let i = 0;
    let total = 0;
    Object.values(cart).forEach(item => {
        i++;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${i}</td>
            <td>${escapeHtml(item.name)}</td>
            <td>${escapeHtml(item.barcode)}</td>
            <td>$${Number(item.price).toFixed(2)}</td>
            <td>
                <input type="number" min="1" value="${item.qty}" data-id="${item.id}" class="form-control qty-input" style="width:90px;">
            </td>
            <td>$${(item.price * item.qty).toFixed(2)}</td>
            <td class="no-print">
                <button class="btn btn-sm btn-outline-secondary btn-edit" data-id="${item.id}">تعديل</button>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${item.id}">حذف</button>
            </td>
        `;
        cartBody.appendChild(row);
        total += item.price * item.qty;
    });

    itemsCountEl.textContent = Object.keys(cart).length;
    totalPriceEl.textContent = total.toFixed(2);

    document.querySelectorAll('.qty-input').forEach(inp => {
        inp.addEventListener('change', function(){
            const id = this.getAttribute('data-id');
            let v = parseInt(this.value) || 1;
            if (v < 1) v = 1;
            cart[id].qty = v;
            updateCartUI();
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(){
            const id = this.getAttribute('data-id');
            delete cart[id];
            updateCartUI();
        });
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function(){
            const id = this.getAttribute('data-id');
            const newName = prompt('عدل اسم المنتج:', cart[id].name);
            if (newName !== null) {
                cart[id].name = newName.trim() === '' ? cart[id].name : newName;
            }
            const newPrice = prompt('عدل السعر (بالدولار):', cart[id].price);
            if (newPrice !== null) {
                const p = parseFloat(newPrice);
                if (!isNaN(p) && p >= 0) cart[id].price = p;
            }
            updateCartUI();
        });
    });
}

function escapeHtml(text){
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function fetchProduct(barcode){
    const formData = new FormData();
    formData.append('barcode', barcode);

    return fetch('get_product.php', {
        method: 'POST',
        body: formData
    }).then(r => r.json());
}


let typingTimer;
const doneTypingInterval = 200; 

barcodeInput.addEventListener('keydown', function(e){
    if (e.key === 'Enter') {
        e.preventDefault();
        const code = barcodeInput.value.trim();
        if (code !== '') handleScan(code);
        barcodeInput.value = '';
        return;
    }
});

barcodeInput.addEventListener('input', function(){
    clearTimeout(typingTimer);
    typingTimer = setTimeout(function(){
        const v = barcodeInput.value.trim();
        if (v !== '') {
            handleScan(v);
            barcodeInput.value = '';
        }
    }, doneTypingInterval);
});

function handleScan(code){
    scanResult.innerHTML = '<div class="text-muted">جارٍ البحث...</div>';
    fetchProduct(code).then(json => {
        if (json.ok) {
            const p = json.product;
            if (cart[p.id]) {
                cart[p.id].qty += 1;
            } else {
                cart[p.id] = { id: p.id, name: p.name, barcode: p.barcode, price: parseFloat(p.price), qty: 1 };
            }
            updateCartUI();
            scanResult.innerHTML = `<div class="text-success">✔ تم إضافة: ${escapeHtml(p.name)} — $${Number(p.price).toFixed(2)}</div>`;
            beep('ok');
        } else {
            scanResult.innerHTML = `<div class="text-danger">✖ ${escapeHtml(json.error || 'Product not found')}</div>`;
            beep('err');
        }
    }).catch(err => {
        scanResult.innerHTML = `<div class="text-danger">خطأ في الاتصال</div>`;
        beep('err');
    });
}

document.getElementById('clearCartBtn').addEventListener('click', function(){
    if (confirm('هل تريد مسح الفاتورة؟')) {
        cart = {};
        updateCartUI();
    }
});

document.getElementById('printBtn').addEventListener('click', function(){
    if (Object.keys(cart).length === 0) {
        alert('الفاتورة فارغة!');
        return;
    }

    let html = `
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="utf-8">
            <title>فاتورة</title>
            <style>
                body{font-family: Arial, sans-serif; direction:rtl; margin:20px;}
                h2{text-align:center;}
                table{width:100%;border-collapse:collapse;margin-top:20px;}
                th,td{border:1px solid #333;padding:8px;text-align:center;}
                .right{text-align:right;}
                .total{font-size:20px;font-weight:700;margin-top:20px;text-align:center;}
            </style>
        </head>
        <body>
            <h2>فاتورة مبيعات</h2>
            <div>التاريخ: ${new Date().toLocaleString()}</div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>باركود</th>
                        <th>السعر</th>
                        <th>الكمية</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
    `;

    let cnt = 0;
    let total = 0;
    Object.values(cart).forEach(it => {
        cnt++;
        html += `<tr>
                    <td>${cnt}</td>
                    <td>${escapeHtml(it.name)}</td>
                    <td>${escapeHtml(it.barcode)}</td>
                    <td>$${Number(it.price).toFixed(2)}</td>
                    <td>${it.qty}</td>
                    <td>$${(it.price * it.qty).toFixed(2)}</td>
                 </tr>`;
        total += it.price * it.qty;
    });

    html += `
                </tbody>
            </table>
            <div class="total">الإجمالي: $${total.toFixed(2)}</div>
            <div style="text-align:center;margin-top:30px;">شكراً لتسوقكم معنا</div>
        </body>
        </html>
    `;

    const w = window.open('', '_blank');
    w.document.write(html);
    w.document.close();
    w.focus();
    w.print();
});
</script>

</body>
</html>
