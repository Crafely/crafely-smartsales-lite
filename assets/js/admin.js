document.addEventListener("DOMContentLoaded", function () {
    function fixAdminLayout() {
        let adminMenu = document.getElementById("adminmenu");
        let wpBody = document.getElementById("wpbody");

        if (adminMenu) {
            adminMenu.style.marginTop = "32px";
        }
        if (wpBody) {
            wpBody.style.paddingTop = "32px";
        }

        if (window.innerWidth <= 782) {
            if (adminMenu) adminMenu.style.marginTop = "46px";
            if (wpBody) wpBody.style.paddingTop = "46px";
        }
    }

    fixAdminLayout();
    window.addEventListener("resize", fixAdminLayout);
    setTimeout(fixAdminLayout, 500);
});
