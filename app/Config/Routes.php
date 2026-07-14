<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::webPage');

$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::loginPost');
$routes->get('/logout', 'Auth::logout');

$routes->get('/home', 'Home::index');

$routes->get('/servicios', 'ServiciosContratados::index');
$routes->get('/servicios/contratados', 'ServiciosContratados::index');
$routes->get('/servicios/contratados/listar', 'ServiciosContratados::listar');
$routes->get('/servicios/contratados/obtener/(:num)', 'ServiciosContratados::obtener/$1');
$routes->post('/servicios/contratados/guardar', 'ServiciosContratados::guardar');
$routes->post('/servicios/contratados/actualizar/(:num)', 'ServiciosContratados::actualizar/$1');
$routes->get('/servicios/contratados/eliminar/(:num)', 'ServiciosContratados::eliminar/$1');

$routes->get('/servicios/tipos', 'TiposServicio::index');
$routes->get('/servicios/tipos/listar', 'TiposServicio::listar');
$routes->get('/servicios/tipos/obtener/(:num)', 'TiposServicio::obtener/$1');
$routes->post('/servicios/tipos/guardar', 'TiposServicio::guardar');
$routes->post('/servicios/tipos/actualizar/(:num)', 'TiposServicio::actualizar/$1');
$routes->get('/servicios/tipos/eliminar/(:num)', 'TiposServicio::eliminar/$1');

$routes->get('/comprobantes', 'Comprobantes::index');
$routes->get('/comprobantes/ventas', 'Comprobantes::ventas');
$routes->get('/comprobantes/ventas/listar', 'Comprobantes::listar');
$routes->get('/comprobantes/crear', 'Comprobantes::crear');
$routes->get('/comprobantes/tipos-por-sede', 'Comprobantes::tiposPorSede');
$routes->get('/comprobantes/obtener-correlativo', 'Comprobantes::obtenerCorrelativo');
$routes->get('/comprobantes/obtener-cliente', 'Comprobantes::obtenerCliente');
$routes->post('/comprobantes/guardar', 'Comprobantes::guardar');
$routes->post('/comprobantes/guardar-cliente', 'Comprobantes::guardarClienteRapido');

$routes->get('/tarifas', 'TarifasMensuales::index');
$routes->get('/tarifas/mensuales', 'TarifasMensuales::index');
$routes->get('/tarifas/mensuales/listar', 'TarifasMensuales::listar');
$routes->get('/tarifas/mensuales/obtener/(:num)', 'TarifasMensuales::obtener/$1');
$routes->post('/tarifas/mensuales/guardar', 'TarifasMensuales::guardar');
$routes->post('/tarifas/mensuales/actualizar/(:num)', 'TarifasMensuales::actualizar/$1');
$routes->get('/tarifas/mensuales/eliminar/(:num)', 'TarifasMensuales::eliminar/$1');

$routes->get('/tarifas/anuales', 'TarifasAnuales::index');
$routes->get('/tarifas/anuales/listar', 'TarifasAnuales::listar');
$routes->get('/tarifas/anuales/obtener/(:num)', 'TarifasAnuales::obtener/$1');
$routes->post('/tarifas/anuales/guardar', 'TarifasAnuales::guardar');
$routes->post('/tarifas/anuales/actualizar/(:num)', 'TarifasAnuales::actualizar/$1');
$routes->get('/tarifas/anuales/eliminar/(:num)', 'TarifasAnuales::eliminar/$1');

$routes->get('/tarifas/vigentes', 'TarifasVigentes::index');
$routes->get('/tarifas/vigentes/listar', 'TarifasVigentes::listar');

$routes->get('/cobros', 'CobrosMensuales::index');
$routes->get('/cobros/deuda', 'DeudaAcumulada::index');
$routes->get('/cobros/deuda/listar', 'DeudaAcumulada::listar');
$routes->get('/cobros/mensuales', 'CobrosMensuales::index');
$routes->get('/cobros/mensuales/listar', 'CobrosMensuales::listar');
$routes->get('/cobros/mensuales/obtener/(:num)', 'CobrosMensuales::obtener/$1');
$routes->get('/cobros/mensuales/obtener-servicios', 'CobrosMensuales::obtenerServicios');
$routes->post('/cobros/mensuales/guardar', 'CobrosMensuales::guardar');
$routes->post('/cobros/mensuales/actualizar/(:num)', 'CobrosMensuales::actualizar/$1');
$routes->get('/cobros/mensuales/eliminar/(:num)', 'CobrosMensuales::eliminar/$1');

$routes->get('/cobros/pagos', 'PagosAplicados::index');
$routes->get('/cobros/pagos/listar', 'PagosAplicados::listar');
$routes->get('/cobros/pagos/obtener/(:num)', 'PagosAplicados::obtener/$1');
$routes->post('/cobros/pagos/guardar', 'PagosAplicados::guardar');
$routes->post('/cobros/pagos/actualizar/(:num)', 'PagosAplicados::actualizar/$1');
$routes->get('/cobros/pagos/eliminar/(:num)', 'PagosAplicados::eliminar/$1');

$routes->get('/cobros/morosidad', 'ReporteMorosidad::index');
$routes->get('/cobros/morosidad/listar', 'ReporteMorosidad::listar');

$routes->get('/contratos', 'Contratos::firmados');
$routes->get('/contratos/firmados', 'Contratos::firmados');
$routes->get('/contratos/firmados/listar', 'Contratos::listar');
$routes->get('/contratos/vigentes', 'Contratos::vigentes');
$routes->get('/contratos/vigentes/listar', 'Contratos::listarVigentes');
$routes->get('/contratos/vencidos', 'Contratos::vencidos');
$routes->get('/contratos/vencidos/listar', 'Contratos::listarVencidos');
$routes->get('/contratos/obtener/(:num)', 'Contratos::obtener/$1');
$routes->post('/contratos/guardar', 'Contratos::guardar');
$routes->post('/contratos/actualizar/(:num)', 'Contratos::actualizar/$1');
$routes->get('/contratos/eliminar/(:num)', 'Contratos::eliminar/$1');

$routes->get('/documentos', 'Documentos::archivos');
$routes->get('/documentos/archivos', 'Documentos::archivos');
$routes->get('/documentos/archivos/listar', 'Documentos::listar');
$routes->get('/documentos/vencidos', 'Documentos::vencidos');
$routes->get('/documentos/vencidos/listar', 'Documentos::listarVencidos');
$routes->get('/documentos/obtener/(:num)', 'Documentos::obtener/$1');
$routes->post('/documentos/guardar', 'Documentos::guardar');
$routes->post('/documentos/actualizar/(:num)', 'Documentos::actualizar/$1');
$routes->get('/documentos/eliminar/(:num)', 'Documentos::eliminar/$1');

$routes->get('/periodos', 'PeriodosContables::apertura');
$routes->get('/periodos/apertura', 'PeriodosContables::apertura');
$routes->get('/periodos/apertura/listar', 'PeriodosContables::listar');
$routes->get('/periodos/cierre', 'PeriodosContables::cierre');
$routes->get('/periodos/cierre/listar', 'PeriodosContables::listarPendientes');
$routes->get('/periodos/ple', 'PeriodosContables::ple');
$routes->get('/periodos/ple/listar', 'PeriodosContables::listarCerrados');
$routes->get('/periodos/pdt', 'PeriodosContables::pdt');
$routes->get('/periodos/pdt/listar', 'PeriodosContables::listarCerrados');
$routes->get('/periodos/obtener/(:num)', 'PeriodosContables::obtener/$1');
$routes->post('/periodos/guardar', 'PeriodosContables::guardar');
$routes->post('/periodos/actualizar/(:num)', 'PeriodosContables::actualizar/$1');
$routes->get('/periodos/cerrar/(:num)', 'PeriodosContables::cerrar/$1');
$routes->get('/periodos/presentar/(:num)', 'PeriodosContables::presentar/$1');
$routes->get('/periodos/eliminar/(:num)', 'PeriodosContables::eliminar/$1');

$routes->get('/notas', 'Notas::generales');
$routes->get('/notas/generales', 'Notas::generales');
$routes->get('/notas/generales/listar', 'Notas::listar/general');
$routes->get('/notas/cobranza', 'Notas::cobranza');
$routes->get('/notas/cobranza/listar', 'Notas::listar/cobranza');
$routes->get('/notas/tributaria', 'Notas::tributaria');
$routes->get('/notas/tributaria/listar', 'Notas::listar/tributaria');
$routes->get('/notas/laboral', 'Notas::laboral');
$routes->get('/notas/laboral/listar', 'Notas::listar/laboral');
$routes->get('/notas/obtener/(:num)', 'Notas::obtener/$1');
$routes->post('/notas/guardar', 'Notas::guardar');
$routes->post('/notas/actualizar/(:num)', 'Notas::actualizar/$1');
$routes->get('/notas/eliminar/(:num)', 'Notas::eliminar/$1');

$routes->get('/tareas', 'Tareas::mias');
$routes->get('/tareas/mias', 'Tareas::mias');
$routes->get('/tareas/mias/listar', 'Tareas::listar/todas');
$routes->get('/tareas/asignadas', 'Tareas::asignadas');
$routes->get('/tareas/asignadas/listar', 'Tareas::listar/asignadas');
$routes->get('/tareas/creadas', 'Tareas::creadas');
$routes->get('/tareas/creadas/listar', 'Tareas::listar/creadas');
$routes->get('/tareas/obtener/(:num)', 'Tareas::obtener/$1');
$routes->post('/tareas/guardar', 'Tareas::guardar');
$routes->post('/tareas/actualizar/(:num)', 'Tareas::actualizar/$1');
$routes->get('/tareas/completar/(:num)', 'Tareas::completar/$1');
$routes->get('/tareas/eliminar/(:num)', 'Tareas::eliminar/$1');

$routes->get('/clientes', 'Clientes::index');
$routes->get('/clientes/listar', 'Clientes::listar');
$routes->get('/clientes/obtener/(:num)', 'Clientes::obtener/$1');
$routes->post('/clientes/guardar', 'Clientes::guardar');
$routes->post('/clientes/actualizar/(:num)', 'Clientes::actualizar/$1');
$routes->get('/clientes/eliminar/(:num)', 'Clientes::eliminar/$1');

$routes->get('/clientes/contactos', 'Contactos::index');
$routes->get('/clientes/contactos/listar', 'Contactos::listar');
$routes->get('/clientes/contactos/obtener/(:num)', 'Contactos::obtener/$1');
$routes->post('/clientes/contactos/guardar', 'Contactos::guardar');
$routes->post('/clientes/contactos/actualizar/(:num)', 'Contactos::actualizar/$1');
$routes->get('/clientes/contactos/eliminar/(:num)', 'Contactos::eliminar/$1');

$routes->get('/clientes/direcciones', 'Direcciones::index');
$routes->get('/clientes/direcciones/listar', 'Direcciones::listar');
$routes->get('/clientes/direcciones/obtener/(:num)', 'Direcciones::obtener/$1');
$routes->post('/clientes/direcciones/guardar', 'Direcciones::guardar');
$routes->post('/clientes/direcciones/actualizar/(:num)', 'Direcciones::actualizar/$1');
$routes->get('/clientes/direcciones/eliminar/(:num)', 'Direcciones::eliminar/$1');

$routes->get('/clientes/actividades', 'Actividades::index');
$routes->get('/clientes/actividades/listar', 'Actividades::listar');
$routes->get('/clientes/actividades/obtener', 'Actividades::obtener');
$routes->post('/clientes/actividades/guardar', 'Actividades::guardar');
$routes->post('/clientes/actividades/actualizar', 'Actividades::actualizar');
$routes->get('/clientes/actividades/eliminar', 'Actividades::eliminar');

$routes->get('/clientes/datos-tributarios/obtener/(:num)', 'DatosTributarios::obtener/$1');
$routes->post('/clientes/datos-tributarios/guardar', 'DatosTributarios::guardar');

$routes->get('/clientes/regimenes', 'Regimenes::index');
$routes->get('/clientes/regimenes/listar', 'Regimenes::listar');

$routes->get('/roles/modulos', 'Modulos::index');
$routes->get('/roles/modulos/listar', 'Modulos::listar');
$routes->get('/roles/modulos/obtener/(:num)', 'Modulos::obtener/$1');
$routes->post('/roles/modulos/guardar', 'Modulos::guardar');
$routes->post('/roles/modulos/actualizar/(:num)', 'Modulos::actualizar/$1');
$routes->get('/roles/modulos/eliminar/(:num)', 'Modulos::eliminar/$1');

$routes->get('/roles/permisos', 'Permisos::index');
$routes->get('/roles/permisos/obtener', 'Permisos::obtener');
$routes->post('/roles/permisos/guardar', 'Permisos::guardar');

$routes->get('/roles', 'Roles::index');
$routes->get('/roles/listar', 'Roles::listar');
$routes->get('/roles/obtener/(:num)', 'Roles::obtener/$1');
$routes->post('/roles/guardar', 'Roles::guardar');
$routes->post('/roles/actualizar/(:num)', 'Roles::actualizar/$1');
$routes->get('/roles/eliminar/(:num)', 'Roles::eliminar/$1');

$routes->get('/usuarios', 'Usuarios::index');
$routes->get('/usuarios/listar', 'Usuarios::listar');
$routes->get('/usuarios/obtener/(:num)', 'Usuarios::obtener/$1');
$routes->post('/usuarios/guardar', 'Usuarios::guardar');
$routes->post('/usuarios/actualizar/(:num)', 'Usuarios::actualizar/$1');
$routes->get('/usuarios/eliminar/(:num)', 'Usuarios::eliminar/$1');

$routes->get('/reportes', 'ReporteFacturacion::index');
$routes->get('/reportes/facturacion', 'ReporteFacturacion::index');
$routes->get('/reportes/facturacion/listar', 'ReporteFacturacion::listar');
$routes->get('/reportes/cobranza', 'ReporteCobranza::index');
$routes->get('/reportes/cobranza/listar', 'ReporteCobranza::listar');
$routes->get('/reportes/morosidad', 'ReporteMorosidad::index');
$routes->get('/reportes/morosidad/listar', 'ReporteMorosidad::listar');
$routes->get('/reportes/regimenes', 'ReporteRegimenes::index');
$routes->get('/reportes/regimenes/listar', 'ReporteRegimenes::listar');

$routes->get('/auditoria', 'Auditoria::index');
$routes->get('/auditoria/logs', 'Auditoria::logs');
$routes->get('/auditoria/logs/listar', 'Auditoria::listar');
$routes->get('/auditoria/config', 'Auditoria::config');

$routes->get('/mensajes', 'Mensajes::listado');
$routes->get('/mensajes/crear', 'Mensajes::crear');
$routes->post('/mensajes/guardar', 'Mensajes::guardar');
$routes->get('/mensajes/listado', 'Mensajes::listado');
$routes->get('/mensajes/listar', 'Mensajes::listar');
$routes->get('/mensajes/obtener/(:num)', 'Mensajes::obtener/$1');
$routes->post('/mensajes/actualizar/(:num)', 'Mensajes::actualizar/$1');
$routes->get('/mensajes/eliminar/(:num)', 'Mensajes::eliminar/$1');

$routes->get('/consultas/api/(:any)/(:any)', 'Consultas::api_dni_ruc/$1/$2');

$routes->get('/vencimientos', 'Vencimientos::index');
$routes->get('/vencimientos/listar', 'Vencimientos::listarCronograma');
$routes->post('/vencimientos/guardar', 'Vencimientos::guardarCronograma');
$routes->get('/vencimientos/eliminar/(:num)', 'Vencimientos::eliminar/$1');
$routes->get('/vencimientos/consultar', 'Vencimientos::consultar');

$routes->get('/empresa', 'Empresa::index');
$routes->post('/empresa/guardar', 'Empresa::guardarEmpresa');
$routes->get('/empresa/sedes/listar', 'Empresa::listarSedes');
$routes->get('/empresa/sedes/obtener/(:num)', 'Empresa::obtenerSede/$1');
$routes->post('/empresa/sedes/guardar', 'Empresa::guardarSede');
$routes->post('/empresa/sedes/actualizar/(:num)', 'Empresa::actualizarSede/$1');
$routes->get('/empresa/sedes/eliminar/(:num)', 'Empresa::eliminarSede/$1');
$routes->get('/empresa/correlativos/listar', 'Empresa::listarCorrelativos');
$routes->get('/empresa/correlativos/obtener/(:num)', 'Empresa::obtenerCorrelativo/$1');
$routes->post('/empresa/correlativos/guardar', 'Empresa::guardarCorrelativo');
$routes->post('/empresa/correlativos/actualizar/(:num)', 'Empresa::actualizarCorrelativo/$1');
$routes->get('/empresa/correlativos/eliminar/(:num)', 'Empresa::eliminarCorrelativo/$1');

$routes->get('/alertas', 'Alertas::vencimientoContrato');
$routes->get('/alertas/vencimiento-contrato', 'Alertas::vencimientoContrato');
$routes->get('/alertas/vencimiento-contrato/listar', 'Alertas::listar/vencimiento_contrato');
$routes->get('/alertas/vencimiento-documento', 'Alertas::vencimientoDocumento');
$routes->get('/alertas/vencimiento-documento/listar', 'Alertas::listar/vencimiento_documento');
$routes->get('/alertas/cobro-vencido', 'Alertas::cobroVencido');
$routes->get('/alertas/cobro-vencido/listar', 'Alertas::listar/cobro_vencido');
$routes->get('/alertas/declaracion', 'Alertas::declaracion');
$routes->get('/alertas/declaracion/listar', 'Alertas::listar/declaracion_proxima');
$routes->get('/alertas/cumpleanos', 'Alertas::cumpleanos');
$routes->get('/alertas/cumpleanos/listar', 'Alertas::listar/cumpleanos');
$routes->get('/alertas/obtener/(:num)', 'Alertas::obtener/$1');
$routes->post('/alertas/guardar', 'Alertas::guardar');
$routes->post('/alertas/actualizar/(:num)', 'Alertas::actualizar/$1');
$routes->get('/alertas/vista/(:num)', 'Alertas::vista/$1');
$routes->get('/alertas/resolver/(:num)', 'Alertas::resolver/$1');
$routes->get('/alertas/descartar/(:num)', 'Alertas::descartar/$1');
