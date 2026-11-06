/* ===== 1. MOBILE MENU (all pages) =====
   Clicking the ☰ button adds or removes the class "open". */
const menuToggle = document.getElementById("menuToggle");
const navLinks = document.getElementById("navLinks");

if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", function () {
    navLinks.classList.toggle("open");
  });
}

/* ===== 2. MENU PRODUCTS (Menu page only) =====
   Later, this list will come from your MySQL database with PHP. */
const filters = document.getElementById("filters");
const productGrid = document.getElementById("productGrid");

if (filters && productGrid) {
  const products = [
    { name: "Pandesal",        category: "Bread",  price: 5,   emoji: "🍞", desc: "Soft, warm, and slightly sweet. Baked every morning." },
    { name: "Ensaymada",       category: "Pastry", price: 45,  emoji: "🥐", desc: "Buttery brioche topped with sugar and cheese." },
    { name: "Choco Cupcake",   category: "Cake",   price: 55,  emoji: "🧁", desc: "Moist chocolate cupcake with creamy frosting." },
    { name: "Ube Cheese Roll", category: "Bread",  price: 35,  emoji: "🥖", desc: "Ube-filled bread roll with melted cheese." },
    { name: "Egg Pie",         category: "Pastry", price: 120, emoji: "🥧", desc: "Creamy custard in a flaky golden crust." },
    { name: "Mocha Cake",      category: "Cake",   price: 350, emoji: "🎂", desc: "Light coffee sponge with mocha cream." }
  ];

  const categories = ["All", "Bread", "Pastry", "Cake"];

  // Draw the product cards for the chosen category
  function showProducts(category) {
    productGrid.innerHTML = "";

    products.forEach(function (p) {
      if (category === "All" || p.category === category) {
        const card = document.createElement("div");
        card.className = "card product";
        card.innerHTML =
          '<div class="emoji">' + p.emoji + "</div>" +
          "<h3>" + p.name + "</h3>" +
          "<p>" + p.desc + "</p>" +
          '<p class="price">&#8369;' + p.price + "</p>";
        productGrid.appendChild(card);
      }
    });
  }

  // Draw the filter buttons
  categories.forEach(function (cat) {
    const btn = document.createElement("button");
    btn.className = "filter-btn" + (cat === "All" ? " active" : "");
    btn.textContent = cat;

    btn.addEventListener("click", function () {
      document.querySelectorAll(".filter-btn").forEach(function (b) {
        b.classList.remove("active");
      });
      btn.classList.add("active");
      showProducts(cat);
    });

    filters.appendChild(btn);
  });

  showProducts("All");
}

/* ===== 3. CONTACT FORM CHECK (Contact page only) ===== */
const form = document.getElementById("contactForm");
const formMessage = document.getElementById("formMessage");

if (form && formMessage) {
  form.addEventListener("submit", function (event) {
    event.preventDefault(); // stop the page from reloading

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const msg = document.getElementById("msg").value.trim();

    if (name === "" || email === "" || msg === "") {
      formMessage.className = "message error";
      formMessage.textContent = "Please fill in your name, email, and message.";
    } else if (!email.includes("@") || !email.includes(".")) {
      formMessage.className = "message error";
      formMessage.textContent = "Please enter a valid email address.";
    } else {
      formMessage.className = "message ok";
      formMessage.textContent = "Thanks, " + name + "! We got your message.";
      form.reset();
    }
  });
}