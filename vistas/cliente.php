<?php
//activamos almacenamiento en el buffer
ob_start();
session_start();
if (!isset($_SESSION['nombre'])) {
  header("Location: login.html");
} else {

  require 'header.php';
  if ($_SESSION['ventas'] == 1) {
    ?>
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">

        <!-- Default box -->
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Clientes

                  <button class="btn btn-success btn-sm" onclick="mostrarform(true)" id="btnagregar"><i
                      class="fa fa-plus-circle"></i> Nuevo</button>
                </h1>
                <div class="box-tools pull-right">

                </div>
              </div>
              <!--box-header-->
              <!--centro-->
              <div class="panel-body table-responsive" id="listadoregistros">
                <div class="row" style="margin-bottom: 10px;">
                  <!-- Campo Buscar -->
                  <div class="col-md-12">
                    <input type="text" id="buscarTabla" class="form-control" placeholder="Buscar en la tabla..."
                      onkeyup="buscarEnTabla(this.value, tabla)">
                  </div>
                </div>
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                  <thead>
                    <th>Opciones</th>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Numero</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Número</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                  </tfoot>
                </table>
              </div>
              <div class="panel-body" style="height: 400px;" id="formularioregistros">
                <form action="" name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Nombre</label>
                    <input class="form-control" type="hidden" name="idpersona" id="idpersona">
                    <input class="form-control" type="hidden" name="tipo_persona" id="tipo_persona" value="Cliente">
                    <input class="form-control" type="text" name="nombre" id="nombre" maxlength="100"
                      placeholder="Nombre del cliente" required>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Tipo Documento</label>
                    <select class="form-control select-picker" name="tipo_documento" id="tipo_documento" required>
                      <option value="DNI">DNI</option>
                      <option value="RUC">RUC</option>
                      <option value="CEDULA">CEDULA</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Número Documento</label>
                    <input class="form-control" type="text" name="num_documento" id="num_documento" maxlength="20"
                      placeholder="Número de Documento">
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Dirección</label>
                    <input class="form-control" type="text" name="direccion" id="direccion" maxlength="70"
                      placeholder="Direccion">
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Teléfono(*)</label>
                    <input class="form-control" type="number" name="telefono" id="telefono" maxlength="20"
                      placeholder="Número de Telefono" required>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Email</label>
                    <input class="form-control" type="email" name="email" id="email" maxlength="50" placeholder="Email">
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i>
                      Guardar</button>

                    <button class="btn btn-danger" onclick="cancelarform()" type="button"><i
                        class="fa fa-arrow-circle-left"></i> Cancelar</button>
                  </div>
                </form>
              </div>
              <!--fin centro-->
            </div>
          </div>
        </div>
        <!-- /.box -->

      </section>
      <!-- /.content -->
    </div>
    <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <script src="scripts/cliente.js"></script>
  <?php
}

ob_end_flush();
?>