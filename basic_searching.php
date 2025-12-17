<!DOCTYPE html>
<html>
<head>
    <style>
        #suggestions {
            position: absolute;
            top: 28%;
            left: 0;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 20px;
            background: #fff;
            z-index: 1000;
            display: none;
        }

        .suggestion-item {
            padding: 8px;
            cursor: pointer;
        }

        .suggestion-item:hover {
            background-color: #eee;
            border-radius: 20px;
        }

        .search-wrapper {
            position: relative;
            max-width: 400px;
            margin: 50px auto;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
        }

        .no-click {
            pointer-events: none;
            color: black;
            cursor: default;
        }

    </style>
</head>
<body>

<form method="GET" autocomplete="off" id="searching"></form>

<div class="search-bar">
    <input form="searching" type="text" id="search" name="search" placeholder="Search for something...">
    <div id="suggestions"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("search");
    const suggestions = document.getElementById("suggestions");

    input.addEventListener("input", function () {
        const query = this.value.trim();
        if (query !== "") {
            fetch(`fetch.php?search=${encodeURIComponent(query)}`)
                .then(res => res.text())
                .then(data => {
                    suggestions.innerHTML = data;
                    suggestions.style.display = "block";

                    document.querySelectorAll(".suggestion-item").forEach(item => {
                        item.addEventListener("click", function () {
                            const id = this.getAttribute("data-id");
                            if (id) {
                                window.location.href = `product/product_${id}.php`;
                            }
                        });
                    });
                });
        } else {
            suggestions.style.display = "none";
        }
    });

    document.addEventListener("click", function (e) {
        if (!document.querySelector(".search-wrapper").contains(e.target)) {
            suggestions.style.display = "none";
        }
    });
});
</script>

</body>
</html>
