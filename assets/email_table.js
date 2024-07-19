document.addEventListener("DOMContentLoaded", function () {
  let currentPage = 1;
  let currentSearch = "";
  let currentSort = "id";
  let currentOrder = "ASC";

  function loadEmails(page = 1, search = "", sort = "id", order = "asc") {
    document.getElementById("skeleton").classList.add("fade-in");
    document.getElementById("skeleton").classList.remove("hidden", "fade-out");
    document
      .getElementById("email-table-list")
      .classList.add("hidden", "fade-out");
    fetch(
      `/mail?page=${page}&search=${encodeURIComponent(
        search
      )}&sort=${sort}&order=${order}`,
      {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      }
    )
      .then((response) => response.json())
      .then((data) => {
        document.getElementById("email-table-list").innerHTML = data.html;
        //updatePagination(data.current_page, data.total_pages, sort, order);
      })
      .finally(() => {
        // Remove a classe de carregamento
        document.getElementById("skeleton").classList.add("hidden", "fade-out");
        document
          .getElementById("email-table-list")
          .classList.remove("hidden", "fade-out");

        document.getElementById("email-table-list").classList.add("fade-in");
      });
  }

  function changeStatus(id, status) {
    fetch(`/mail/status/${id}`, {
      method: "PATCH",
      body: JSON.stringify({ status }),
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
      })
      .catch((error) => {
        console.error(error);
      });
  }

  document.getElementById("search").addEventListener("keyup", function () {
    currentSearch = this.value;
    currentPage = 1;
    loadEmails(currentPage, currentSearch, currentSort, currentOrder);
  });

  document.addEventListener("click", function (event) {
    // console.log("Clik! ");
    // console.log(event.target);
    if (event.target.matches(".pagination a")) {
      event.preventDefault();
      currentPage = event.target.getAttribute("data-page");
      loadEmails(currentPage, currentSearch, currentSort, currentOrder);
    }

    if (event.target.matches(".sortable")) {
      // console.log("Clik sortable");
      event.preventDefault();
      currentSort = event.target.dataset.sort;
      currentOrder = event.target.dataset.order;
      console.log(currentOrder);
      // const sortField = event.target.dataset.sort;
      // currentOrder = document.querySelector(".sort-icon.sort-asc") ? "asc" : "desc";
      const newOrder = currentOrder === "ASC" ? "DESC" : "ASC";
      console.log(newOrder);
      loadEmails(currentPage, currentSearch, currentSort, newOrder);
    }
  });

  document.addEventListener("change", function (event) {
    if (event.target.matches(".select.status")) {
      const id = event.target.dataset.id;
      const status = event.target.value;
      changeStatus(id, status);
    }
  });
  //loadEmails();
});

console.log("Email table initialized");
