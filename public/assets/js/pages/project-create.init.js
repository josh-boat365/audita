document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    var e = document.querySelectorAll(".needs-validation"),
        v = 10,
        t = new Date,
        a = new Date(t.getFullYear(), t.getMonth(), t.getDate());

    $("#duedate-input").datepicker("setDate", a);

    var projectImageInput = document.querySelector("#project-image-input");
    if (projectImageInput) {
        projectImageInput.addEventListener("change", function () {
            var e = document.querySelector("#projectlogo-img"),
                t = projectImageInput.files[0],
                a = new FileReader;
            a.addEventListener("load", function () {
                e.src = a.result
            }, !1), t && a.readAsDataURL(t)
        });
    }

    var o, r = sessionStorage.getItem("editInputValue");
    if (r) {
        r = JSON.parse(r);
        document.getElementById("formAction").value = "edit";
        document.getElementById("project-id-input").value = r.id;
        document.getElementById("projectlogo-img").src = r.projectLogoImg;
        document.getElementById("projectname-input").value = r.projectTitle;
        document.getElementById("projectdesc-input").value = r.projectDesc;
        document.getElementById("project-status-input").value = r.status;
        $("#duedate-input").datepicker("setDate", r.dueDate);
        Array.from(document.querySelectorAll("#select-element .dropdown-menu ul li a")).forEach(function (o) {
            var n = o.querySelector(".flex-grow-1").innerHTML;
            r.assignedto.map(function (e) {
                var t, a;
                return e.assigneeName == n && (o.classList.add("active"), t = document.getElementById("assignee-member"), o.classList.contains("active") && (a = '<a href="javascript: void(0);" class="avatar-group-item mb-2" data-img="' + e.assigneeImg + '"  data-bs-toggle="tooltip" data-bs-placement="top" title="' + e.assigneeName + '">                        <img src="' + e.assigneeImg + '" alt="" class="rounded-circle avatar-xs" />                        </a>', t.insertAdjacentHTML("beforeend", a))), o
            })
        });
        o = document.querySelectorAll("#select-element .dropdown-menu .dropdown-item.active").length;
        document.getElementById("total-assignee").innerHTML = o;
    }

    Array.prototype.slice.call(e).forEach(function (p) {
        p.addEventListener("submit", function (e) {
            if (p.checkValidity()) {
                e.preventDefault();
                var t = ++v,
                    a = document.getElementById("projectname-input").value,
                    o = document.getElementById("projectdesc-input").value,
                    n = document.getElementById("projectlogo-img").src,
                    r = document.getElementById("project-status-input").value,
                    i = document.getElementById("duedate-input").value,
                    s = [],
                    c = document.querySelectorAll("#select-element .assignto-list li a.active");
                0 < c.length && Array.from(c).forEach(function (e) {
                    var t = e.querySelector(".avatar-xs img").getAttribute("src"),
                        a = e.querySelector(".flex-grow-1").innerHTML,
                        o = {};
                    o.assigneeName = a, o.assigneeImg = t, s.push(o)
                });
                var l, u, d, m, g = document.getElementById("formAction").value;
                return "add" == g ? (null != sessionStorage.getItem("inputValue") ? (l = JSON.parse(sessionStorage.getItem("inputValue")), u = {
                    id: t + 1,
                    projectLogoImg: n,
                    projectTitle: a,
                    projectDesc: o,
                    dueDate: i,
                    status: r,
                    assignedto: s
                }, l.push(u)) : (l = []).push(u = {
                    id: t,
                    projectLogoImg: n,
                    projectTitle: a,
                    projectDesc: o,
                    dueDate: i,
                    status: r,
                    assignedto: s
                }), sessionStorage.setItem("inputValue", JSON.stringify(l))) : "edit" == g ? (d = document.getElementById("project-id-input").value, sessionStorage.getItem("editInputValue") && (m = {
                    id: parseInt(d),
                    projectLogoImg: n,
                    projectTitle: a,
                    projectDesc: o,
                    dueDate: i,
                    status: r,
                    assignedto: s
                }, sessionStorage.setItem("editInputValue", JSON.stringify(m)))) : console.log("Form Action Not Found."), window.location.replace("projects-list.html"), !1
            }
            e.preventDefault(), e.stopPropagation(), p.classList.add("was-validated")
        }, !1)
    });

    Dropzone.autoDiscover = !1;
    new Dropzone("div#myId", {
        url: "https://httpbin.org/post"
    });

    Array.from(document.querySelectorAll("#select-element .assignto-list li a")).forEach(function (i) {
        i.addEventListener("click", function () {
            i.classList.toggle("active");
            var e = document.querySelectorAll("#select-element .assignto-list li a.active");
            document.getElementById("total-assignee").innerHTML = e.length;
            var t, a, o, n = i.querySelector(".avatar-xs img").getAttribute("src"),
                r = document.getElementById("assignee-member");
            i.classList.contains("active") ? (t = i.querySelector(".flex-grow-1").innerHTML, a = '<a href="javascript: void(0);" class="avatar-group-item mb-2" data-img="' + n + '"  data-bs-toggle="tooltip" data-bs-placement="top" title="' + t + '">                <img src="' + n + '" alt="" class="rounded-circle avatar-xs" />                </a>', r.insertAdjacentHTML("beforeend", a)) : Array.from(r.querySelectorAll(".avatar-group-item")).forEach(function (e) {
                var t = e.getAttribute("data-img");
                n == t && e.remove()
            }), o = document.querySelectorAll('[data-bs-toggle="tooltip"]'), [].concat(function (e) {
                {
                    if (Array.isArray(e)) {
                        for (var t = 0, a = Array(e.length); t < e.length; t++) a[t] = e[t];
                        return a
                    }
                    return Array.from(e)
                }
            }(o)).map(function (e) {
                return new bootstrap.Tooltip(e)
            })
        })
    });
});
