<!DOCTYPE html>
<html>
<head>
    <style>
        /* Added relative positioning to wrapper so absolute suggestions align correctly */
        .search-wrapper {
            position: relative;
            max-width: 400px;
            margin: 20px auto; /* Adjusted margin for better fit */
        }

        #suggestions {
            position: absolute;
            top: 100%; /* Pushes it exactly below the input */
            left: 0;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 0 0 10px 10px; /* Rounded corners only on bottom */
            background: #fff;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Adds shadow for depth */
        }

        .suggestion-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover {
            background-color: #f5f5f5;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 20px; /* Matches your style */
            box-sizing: border-box; /* Ensures padding doesn't break width */
        }

        .no-click {
            pointer-events: none;
            color: #999;
            cursor: default;
            font-style: italic;
        }
    </style>
</head>
<body>

<form method="GET" autocomplete="off" id="searching"></form>

<div class="search-wrapper">
    <input form="searching" type="text" id="search" name="search" placeholder="Search for coffee...">
    <div id="suggestions"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("search");
    const suggestions = document.getElementById("suggestions");
    const wrapper = document.querySelector(".search-wrapper");

    input.addEventListener("input", function () {
        const query = this.value.trim();
        
        if (query !== "") {
            fetch(`fetch.php?search=${encodeURIComponent(query)}`)
                .then(res => res.text())
                .then(data => {
                    // Only show if we actually have results
                    if(data.trim() !== "") {
                        suggestions.innerHTML = data;
                        suggestions.style.display = "block";
                        
                        // Attach click events to the new items
                        document.querySelectorAll(".suggestion-item").forEach(item => {
                            item.addEventListener("click", function () {
                                const id = this.getAttribute("data-id");
                                if (id) {
                                    // --- THE FIX ---
                                    // Matches your member.php format: product_detail.php?id=123
                                    window.location.href = `product_detail.php?id=${id}`;
                                }
                            });
                        });
                    } else {
                        suggestions.style.display = "none";
                    }
                })
                .catch(err => console.error("Search error:", err));
        } else {
            suggestions.style.display = "none";
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener("click", function (e) {
        if (!wrapper.contains(e.target)) {
            suggestions.style.display = "none";
        }
    });
});
</script>

</body>
</html>