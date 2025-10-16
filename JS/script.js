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



// Dropdown functionality moved to sidebar.php inline script





const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

if (menuBar && sidebar) {
    menuBar.addEventListener('click', function (){
        sidebar.classList.toggle('hide');
    });
}


const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

if(window.innerWidth < 768 && sidebar) {
    sidebar.classList.add('hide');
} else if (window.innerWidth > 576 && searchButtonIcon && searchForm){
    searchButtonIcon.classList.replace('bx-search', 'bx-x');
    searchForm.classList.remove('show')
}

if (searchButton) {
    searchButton.addEventListener('click', function (e) {
        if (window.innerWidth > 576) {
            e.preventDefault();
            searchForm.classList.toggle('show');
            if(searchForm.classList.contains('show')){
                searchButtonIcon.classList.replace('bx-search', 'bx-x');
            }else{
                searchButtonIcon.classList.replace('bx-x', 'bx-search');
            }
        }
    });
}





