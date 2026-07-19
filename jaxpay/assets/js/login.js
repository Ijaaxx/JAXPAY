// JAXPAY - Login page script (particles, demo fill, submit handler)

document.addEventListener("DOMContentLoaded", function () {
  // Generate particles
  try {
    const container = document.getElementById("particles");
    if (container) {
      container.innerHTML = "";
      for (let i = 0; i < 15; i++) {
        const p = document.createElement("div");
        p.className = "particle";
        const size = Math.random() * 60 + 10;
        p.style.cssText = `
          width:${size}px; height:${size}px;
          left:${Math.random() * 100}%;
          animation-duration:${Math.random() * 15 + 8}s;
          animation-delay:${Math.random() * 10}s;
          opacity:${Math.random() * 0.3 + 0.1};
        `;
        container.appendChild(p);
      }
    }
  } catch (e) {
    console.warn("Particles init failed", e);
  }

  // Demo email click helper
  window.fillEmail = function (email) {
    const el = document.getElementById("emailInput");
    if (el) el.value = email;
  };

  // Login form
  const form = document.getElementById("loginForm");
  if (!form) return;

  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    const emailEl = document.getElementById("emailInput");
    const btn = document.getElementById("btnLogin");
    if (!emailEl || !btn) return;

    const email = emailEl.value.trim();
    if (!email || !email.includes("@")) {
      Swal.fire({
        icon: "warning",
        title: "Email tidak valid",
        text: "Masukkan email terdaftar sebelum mengirim OTP.",
        background: "#1A1A2E",
        color: "#fff",
        confirmButtonColor: "#6C3CE1",
      });
      return;
    }
    btn.disabled = true;
    btn.innerHTML =
      '<i class="fas fa-circle-notch fa-spin"></i> Mengirim OTP...';

    Swal.fire({
      title: "Mengirim OTP...",
      html:
        "Kode OTP sedang dikirim ke<br><strong>" + (email || "") + "</strong>",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
      background: "#1A1A2E",
      color: "#fff",
    });

    try {
      const resp = await fetch("../proses/kirim_otp.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "email=" + encodeURIComponent(email),
      });
      const rawText = await resp.text();
      let data;
      try {
        data = JSON.parse(rawText);
      } catch (parseError) {
        throw new Error(
          "Response bukan JSON. Server mengembalikan: " +
            rawText.trim().slice(0, 300),
        );
      }

      if (!resp.ok) {
        throw new Error(data.message || "Server error: " + resp.status);
      }

      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "OTP Terkirim!",
          html:
            "Kode OTP telah dikirim ke<br><strong>" +
            email +
            "</strong><br><small>Berlaku 5 menit</small>",
          background: "#1A1A2E",
          color: "#fff",
          confirmButtonColor: "#6C3CE1",
          confirmButtonText: "Masukkan OTP",
        }).then(() => {
          window.location.href = "otp.php?email=" + encodeURIComponent(email);
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal!",
          text: data.message || "Email tidak terdaftar di sistem.",
          background: "#1A1A2E",
          color: "#fff",
          confirmButtonColor: "#6C3CE1",
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Kode OTP';
      }
    } catch (err) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text:
          err.message ||
          "Terjadi kesalahan koneksi. Pastikan Apache/XAMPP berjalan dan akses melalui http://localhost/jaxpay/auth/login.php.",
        background: "#1A1A2E",
        color: "#fff",
        confirmButtonColor: "#6C3CE1",
      });
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Kode OTP';
    }
  });
});
