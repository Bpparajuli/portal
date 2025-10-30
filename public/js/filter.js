document.addEventListener("DOMContentLoaded", function () {
    const countryDropdown = $("#country");
    const cityDropdown = $("#city");
    const universityDropdown = $("#university_id");
    const courseDropdown = $("#course_id");

    // Populate dropdown helper
    function populateDropdown(selectElement, data, placeholder) {
        selectElement
            .empty()
            .append(`<option value="">${placeholder}</option>`);
        data.forEach((item) => {
            const value = typeof item === "object" ? item.id : item;
            const text =
                typeof item === "object" ? item.name || item.title : item;
            selectElement.append(`<option value="${value}">${text}</option>`);
        });
    }

    // Country → Cities
    countryDropdown.on("change", function () {
        const country = $(this).val();
        populateDropdown(cityDropdown, [], "All Cities");
        populateDropdown(universityDropdown, [], "All Universities");
        populateDropdown(courseDropdown, [], "All Courses");

        if (country) {
            let url = $(this)
                .data("cities-url")
                .replace(":country", encodeURIComponent(country));
            $.getJSON(url, function (data) {
                populateDropdown(cityDropdown, data, "All Cities");
            });
        }
    });

    // City → Universities
    cityDropdown.on("change", function () {
        const city = $(this).val();
        populateDropdown(universityDropdown, [], "All Universities");
        populateDropdown(courseDropdown, [], "All Courses");

        if (city) {
            let url = $(this)
                .data("universities-url")
                .replace(":city", encodeURIComponent(city));
            $.getJSON(url, function (data) {
                populateDropdown(universityDropdown, data, "All Universities");
            });
        }
    });

    // University → Courses
    universityDropdown.on("change", function () {
        const uniId = $(this).val();
        populateDropdown(courseDropdown, [], "All Courses");

        if (uniId) {
            let url = $(this)
                .data("courses-url")
                .replace(":universityId", encodeURIComponent(uniId));
            $.getJSON(url, function (data) {
                populateDropdown(courseDropdown, data, "All Courses");
            });
        }
    });

    // --- Auto-clear all filters if query params exist ---
    if (window.location.search.length > 0) {
        setTimeout(() => {
            $("#search").val("");
            countryDropdown.val("");
            populateDropdown(cityDropdown, [], "All Cities");
            populateDropdown(universityDropdown, [], "All Universities");
            populateDropdown(courseDropdown, [], "All Courses");
        }, 200);
    }

    console.log("University filter JS loaded ✅");
});
