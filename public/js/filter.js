document.addEventListener("DOMContentLoaded", function () {
    const countryDropdown = $("#country");
    const cityDropdown = $("#city");
    const universityDropdown = $("#university_id");
    const courseTypeDropdown = $("#course_type");
    const courseDropdown = $("#course_id");

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

    // COUNTRY → CITIES
    countryDropdown.on("change", function () {
        const country = $(this).val();
        populateDropdown(cityDropdown, [], "All Cities");
        populateDropdown(universityDropdown, [], "All Universities");
        populateDropdown(courseTypeDropdown, [], "All Types");
        populateDropdown(courseDropdown, [], "All Courses");

        if (country) {
            const url = $(this)
                .data("cities-url")
                .replace(":country", encodeURIComponent(country));
            $.getJSON(url, function (data) {
                populateDropdown(cityDropdown, data, "All Cities");
            });
        }
    });

    // CITY → UNIVERSITIES
    cityDropdown.on("change", function () {
        const city = $(this).val();
        populateDropdown(universityDropdown, [], "All Universities");
        populateDropdown(courseTypeDropdown, [], "All Types");
        populateDropdown(courseDropdown, [], "All Courses");

        if (city) {
            const url = $(this)
                .data("universities-url")
                .replace(":city", encodeURIComponent(city));
            $.getJSON(url, function (data) {
                populateDropdown(universityDropdown, data, "All Universities");
            });
        }
    });

    // UNIVERSITY → COURSE TYPES
    universityDropdown.on("change", function () {
        const uniId = $(this).val();
        populateDropdown(courseTypeDropdown, [], "All Types");
        populateDropdown(courseDropdown, [], "All Courses");

        if (uniId) {
            const url = $(this)
                .data("type-url")
                .replace(":universityId", uniId);
            $.getJSON(url, function (types) {
                populateDropdown(courseTypeDropdown, types, "All Types");
            });
        }
    });

    // COURSE TYPE → COURSES
    courseTypeDropdown.on("change", function () {
        const uniId = universityDropdown.val();
        const courseType = $(this).val();
        populateDropdown(courseDropdown, [], "All Courses");

        if (uniId && courseType) {
            const url = $(this)
                .data("courses-url")
                .replace(":universityId", uniId)
                .replace(":type", courseType);

            $.getJSON(url, function (courses) {
                populateDropdown(courseDropdown, courses, "All Courses");
            });
        }
    });

    console.log("Filter.js loaded ✅");
});
