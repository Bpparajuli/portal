document.addEventListener("DOMContentLoaded", function () {
    const countryDropdown = $("#country");
    const cityDropdown = $("#city");
    const universityDropdown = $("#university_id");
    const courseDropdown = $("#course_id");

    function populateDropdown(selectElement, data, placeholder, selectedValue) {
        selectElement
            .empty()
            .append(`<option value="">${placeholder}</option>`);
        data.forEach((item) => {
            const value = typeof item === "object" ? item.id : item;
            const text =
                typeof item === "object" ? item.name || item.title : item;
            const isSelected = selectedValue && selectedValue == value;
            selectElement.append(
                `<option value="${value}" ${
                    isSelected ? "selected" : ""
                }>${text}</option>`
            );
        });
    }

    // Country → Cities
    countryDropdown.on("change", function () {
        const country = $(this).val();
        populateDropdown(cityDropdown, [], "All Cities", null);
        populateDropdown(universityDropdown, [], "All Universities", null);
        populateDropdown(courseDropdown, [], "All Courses", null);

        if (country) {
            let url = $(this)
                .data("cities-url")
                .replace(":country", encodeURIComponent(country));
            $.getJSON(url, function (data) {
                populateDropdown(cityDropdown, data, "All Cities", null);
            });
        }
    });

    // City → Universities
    cityDropdown.on("change", function () {
        const city = $(this).val();
        populateDropdown(universityDropdown, [], "All Universities", null);
        populateDropdown(courseDropdown, [], "All Courses", null);

        if (city) {
            let url = $(this)
                .data("universities-url")
                .replace(":city", encodeURIComponent(city));
            $.getJSON(url, function (data) {
                populateDropdown(
                    universityDropdown,
                    data,
                    "All Universities",
                    null
                );
            });
        }
    });

    // University → Courses
    universityDropdown.on("change", function () {
        const uniId = $(this).val();
        populateDropdown(courseDropdown, [], "All Courses", null);

        if (uniId) {
            let url = $(this)
                .data("courses-url")
                .replace(":universityId", encodeURIComponent(uniId));
            $.getJSON(url, function (data) {
                populateDropdown(courseDropdown, data, "All Courses", null);
            });
        }
    });
});
