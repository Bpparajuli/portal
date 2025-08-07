const navItems = document.querySelectorAll(".nav-item");

navItems.forEach((navItem, i) => {
    navItem.addEventListener("click", () => {
        navItems.forEach((item, j) => {
            item.className = "nav-item";
        });
        navItem.className = "nav-item active";
    });
});

(function () {
    "use strict";

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll(".needs-validation");

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add("was-validated");
            },
            false
        );
    });
})();
(function () {
    "use strict";
    var forms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add("was-validated");
            },
            false
        );
    });
})();

// Additional functions for handling form submissions in apply_now.php page
document.addEventListener("DOMContentLoaded", function () {
    const universityDropdown = document.getElementById("university");
    const courseDropdown = document.getElementById("course_title"); // Correct ID for the course filter dropdown

    // Capture all course options with their university IDs from the HTML
    // This array will be the source for dynamic filtering
    const allCourseOptions = Array.from(courseDropdown.options).filter(
        (option) => option.value !== ""
    );

    // Store the initial filter values from PHP for re-selection
    const initialUniversityId = "";
    const initialCourseTitle = "";

    function filterCourses() {
        const selectedUniversityId = universityDropdown.value;
        // Clear current options, but keep the "All Courses" default
        courseDropdown.innerHTML = '<option value="">All Courses</option>';

        allCourseOptions.forEach((option) => {
            const courseUniId = option.dataset.universityId; // Get university ID from data attribute

            // If no university is selected (show all courses) OR if the course belongs to the selected university
            if (
                selectedUniversityId === "" ||
                courseUniId === selectedUniversityId
            ) {
                courseDropdown.appendChild(option.cloneNode(true)); // Add a clone of the option
            }
        });

        // After filtering, try to re-select the initially selected course if it's still valid
        // This is for when the page loads with existing filters applied
        if (
            initialCourseTitle &&
            (selectedUniversityId === "" ||
                initialUniversityId === selectedUniversityId)
        ) {
            const courseExistsInFiltered = Array.from(
                courseDropdown.options
            ).some((opt) => opt.value === initialCourseTitle);
            if (courseExistsInFiltered) {
                courseDropdown.value = initialCourseTitle;
            } else {
                courseDropdown.value = ""; // Clear selection if the previous course is not valid for the new university
            }
        } else {
            courseDropdown.value = ""; // Clear selection if university changed or no initial course was selected
        }
    }

    // Event Listener: Call filterCourses when the university dropdown changes
    universityDropdown.addEventListener("change", filterCourses);

    // Initial call to filterCourses when the page loads
    // This ensures the course dropdown is correctly populated based on any pre-selected university filter
    filterCourses();

    // Handle the "Clear Filters" button
    const clearFiltersBtn = document.querySelector('button[type="reset"]');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener("click", function () {
            // Clear all filter inputs
            document.getElementById("search").value = "";
            document.getElementById("agent").value = "";
            document.getElementById("university").value = "";
            // Set course dropdown to "All Courses" and filter dynamically
            document.getElementById("course_title").value = "";
            filterCourses(); // Re-filter courses to show all for no university selected
            document.getElementById("status").value = "";
            document.getElementById("sort_by").value = "created_at"; // Reset to default sort
            document.getElementById("sort_order").value = "DESC"; // Reset to default order

            // Optionally, submit the form to clear GET parameters from the URL
            // If you want to clear the URL parameters, uncomment the line below.
            // this.closest('form').submit();
        });
    }
});
