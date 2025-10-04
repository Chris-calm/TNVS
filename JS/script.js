const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item=> {
    const li = item.parentElement;

    item.addEventListener('click', function () {
        allSideMenu.forEach(i=> {
            i.parentElement.classList.remove('active');
        })
        li.classList.add('active'); 
    })
});



document.addEventListener("DOMContentLoaded", function () {
  const dropdowns = document.querySelectorAll("#sidebar .side-menu li.dropdown");
  const allLinks = document.querySelectorAll("#sidebar .side-menu li a");

  dropdowns.forEach((dropdown) => {
    const toggle = dropdown.querySelector("a"); 

    toggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();


      dropdowns.forEach((item) => {
        if (item !== dropdown) {
          item.classList.remove("open");
        }
      });

      dropdown.classList.toggle("open");
    });
  });


  allLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const parent = link.closest("li.dropdown");

      if (!parent) {
        dropdowns.forEach((dropdown) => dropdown.classList.remove("open"));
      }
    });
  });
});





const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function (){
    sidebar.classList.toggle('hide');
})


if(window.innerWidth < 768) {
    sidebar.classList.add('hide');
} else if (window.innerWidth > 576){
    searchButtonIcon.classList.replace('bx-search', 'bx-x');
    searchForm.classList.remove('show')
}

    const searchButton = document.querySelector('#content nav form .form-input button');
    const searchButtonIcon = document.querySelector('#content nav form .form-input button');
    const searchForm = document.querySelector('#content nav form');

    searchButton.addEventListener('click', function (e) {
        if (window.innerWidth > 576) {
        e.preventDefault();
        searchForm.classList.toggle('show');
        if(searchForm.classList.toggle('show')){
            searchButtonIcon.classList.replace('bx-search', 'bx-x');
        }else{
            searchButtonIcon.classList.replace('bx-x', 'bx-search');
        }
        }
        
    })





