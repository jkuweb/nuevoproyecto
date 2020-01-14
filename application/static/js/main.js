(function(){
    $('[data-toggle="tooltip"]').tooltip();
    $(".side-nav .collapse").on("hide.bs.collapse", function() {                   
        $(this).prev().find(".fa").eq(1).removeClass("fa-angle-right").addClass("fa-angle-down");
    });
    $('.side-nav .collapse').on("show.bs.collapse", function() {                        
        $(this).prev().find(".fa").eq(1).removeClass("fa-angle-down").addClass("fa-angle-right");        
    });
})    
$(document).ready(function() {
    var opciones = {
       language: {
           url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Spanish.json'
       }
    };

    $('#example').DataTable(opciones);    
});

// FUNCIONES AGREGAR MAS 
function agregar() {
    var padre = document.getElementById('padre');
    var hijo = padre.lastElementChild;
    var capa = document.createElement('div');
    capa.setAttribute('class', 'form-group hijo');
    padre.appendChild(capa);
    padre.lastElementChild.innerHTML = hijo.innerHTML;
}

function eliminar(elemento) {
    var hijo = elemento.parentNode.parentNode;
    var padre = document.getElementById('padre');
    var n = padre.children.length;
    if(n > 1) { 
        padre.removeChild(hijo); 
    } else {
        hijo.children[1].children[0].value = '';
    }   
}
// FUNCIONES DATALIST
function changedata(elemento) {
    var dl = document.getElementById('productos');
    for(i=0; i < dl.options.length; i++) {
        if(dl.options[i].value == elemento.value) {
            elemento.value = dl.options[i].text;
            elemento.setAttribute('data-id', dl.options[i].value);
        }
    }
    update_data();
}
        
function update_data() {
    var productos = document.getElementsByName('producto[]');
    var productos_id = document.getElementsByName('producto_id[]');
            
    for(i=0; i < productos.length; i++) {
        productos_id[i].value = productos[i].getAttribute('data-id');
    }
}

