function CrearPantallaCargaUnica() {
  const id = 'loader_' + Date.now() + Math.floor(Math.random() * 1000); // ID único
  const $loader = $(`
		<div id="${id}" class="pantalla-carga-individual" style="
			position: fixed;
			top: 0; left: 0; width: 100%; height: 100%;
			background: #00000033;
			z-index: 9999;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			color: white;
			font-size: 20px;
		">
			<div></div>
			<div class="spinner-border text-light" style="margin-top: 15px;" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
	`);
  $('body').append($loader);
  return id;
}

function ExportarExcelDeTabla() {
  $('.buttons-excel').click();
}

function ArmarNombreDeArchivo(prefijo) {
  // Obtener fecha y hora actual en formato YYYY-MM-DD_HH-MM-SS
  var date = new Date();
  var year = date.getFullYear();
  var month = ('0' + (date.getMonth() + 1)).slice(-2);
  var day = ('0' + date.getDate()).slice(-2);
  var hours = ('0' + date.getHours()).slice(-2);
  var minutes = ('0' + date.getMinutes()).slice(-2);
  var seconds = ('0' + date.getSeconds()).slice(-2);

  return (
    prefijo + '_' + year + '-' + month + '-' + day + '_' + hours + '-' + minutes + '-' + seconds
  );
}

function buscarEnTabla(valor, tabla) {
  tabla.search(valor).draw();
}
