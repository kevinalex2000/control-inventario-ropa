function crearPantallaCargaUnica() {
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
			<div>Cargando...</div>
			<div class="spinner-border text-light" style="margin-top: 15px;" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
	`);
	$('body').append($loader);
	return id;
}