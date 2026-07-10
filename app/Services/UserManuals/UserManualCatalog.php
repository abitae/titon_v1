<?php

namespace App\Services\UserManuals;

use Illuminate\Support\Arr;

class UserManualCatalog
{
    /**
     * @return array<string, mixed>|null
     */
    public function forRoute(?string $routeName): ?array
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        $manualKey = $this->routeMap()[$routeName] ?? $this->fallbackKey($routeName);

        if ($manualKey === null) {
            return null;
        }

        return $this->manuals()[$manualKey] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function manuals(): array
    {
        return [
            'dashboard' => $this->manual(
                title: 'Manual de usuario - Dashboard',
                module: 'Dashboard',
                purpose: 'Presentar una lectura ejecutiva de obras, compras, contratos, pagos y documentos dentro del alcance de la empresa activa o del consolidado gerencial.',
                when: [
                    'Usalo al iniciar la jornada para revisar indicadores clave y detectar pendientes.',
                    'Usalo antes de reuniones de gerencia para exportar el resumen ejecutivo en PDF.',
                    'Usalo para comparar la vista de empresa activa contra el consolidado cuando tu rol lo permita.',
                ],
                steps: [
                    'Confirma el alcance mostrado en la cabecera: empresa activa o consolidado gerencial.',
                    'Revisa las tarjetas KPI para identificar saldos, contratos, pagos vencidos y documentos vencidos.',
                    'Analiza los graficos por mes, estado, ciudad y proveedor para ubicar concentraciones o desviaciones.',
                    'Cambia de modo solo si necesitas comparar informacion multiempresa.',
                    'Exporta el resumen PDF cuando necesites compartir la vista ejecutiva fuera del sistema.',
                ],
                controls: [
                    'Empresa activa: limita todos los indicadores a la compania seleccionada.',
                    'Consolidado gerencial: muestra informacion agregada cuando el usuario tiene permiso.',
                    'Exportar resumen PDF: descarga el reporte ejecutivo del modo seleccionado.',
                    'Graficos y tarjetas: resumen visual de pagos, contratos, obras, proveedores y documentos.',
                ],
                rules: [
                    'El alcance depende de la empresa activa y de los permisos del usuario.',
                    'Los montos reflejan registros existentes en contratos, pagos y cuentas relacionadas.',
                    'Si no ves el modo consolidado, tu rol no tiene acceso gerencial multiempresa.',
                ],
                tips: [
                    'Si un indicador parece incompleto, valida primero la empresa activa.',
                    'Usa el PDF como evidencia de corte, no como reemplazo del registro operativo.',
                ],
                permissions: ['dashboard.ver'],
            ),
            'purchases' => $this->manual(
                title: 'Manual de usuario - Requerimientos de compra',
                module: 'Compras / Requerimientos',
                purpose: 'Registrar solicitudes de bienes o servicios por obra y preparar la informacion que luego se cotizara con proveedores.',
                when: [
                    'Cuando una obra necesita materiales, servicios o equipos que deben pasar por el flujo de compras.',
                    'Cuando se requiere centralizar items, prioridad, solicitante, tipo de costo y descripcion tecnica.',
                    'Cuando se necesita dar seguimiento al estado de la solicitud y sus cotizaciones asociadas.',
                ],
                steps: [
                    'Filtra por codigo, descripcion, estado u obra para revisar solicitudes existentes.',
                    'Presiona Nueva solicitud para registrar obra, solicitante, prioridad, fecha, tipo de costo y descripcion.',
                    'Agrega cada item con producto o servicio, unidad, cantidad, centro de costo, especificacion tecnica y observaciones.',
                    'Guarda la solicitud; el codigo operativo se asigna automaticamente segun la configuracion de correlativos.',
                    'Desde las acciones de la fila edita, elimina o continua el flujo de cotizaciones segun permisos.',
                ],
                controls: [
                    'Buscar: localiza solicitudes por codigo o descripcion.',
                    'Estado y Obra: acotan la tabla para seguimiento operativo.',
                    'Nueva solicitud: abre el formulario principal.',
                    'Agregar item: abre un modal secundario para detallar cada producto o servicio.',
                    'Acciones de fila: editar, eliminar o navegar a etapas relacionadas del flujo.',
                ],
                rules: [
                    'Cada requerimiento debe pertenecer a una obra y tener al menos un item valido.',
                    'La prioridad ayuda a ordenar la atencion, pero no sustituye la aprobacion formal.',
                    'Los estados controlan el avance del flujo y deben actualizarse con criterio operativo.',
                ],
                tips: [
                    'Describe items con suficiente detalle para evitar cotizaciones no comparables.',
                    'Usa el centro de costo UA cuando el gasto deba rastrearse con precision.',
                ],
                permissions: ['purchases.ver', 'purchases.crear', 'purchases.editar', 'purchases.aprobar'],
            ),
            'quotations' => $this->manual(
                title: 'Manual de usuario - Cotizaciones de proveedores',
                module: 'Compras / Cotizaciones',
                purpose: 'Registrar y comparar propuestas de proveedores para un requerimiento especifico.',
                when: [
                    'Cuando ya existe un requerimiento y se recibieron ofertas de proveedores.',
                    'Cuando necesitas adjuntar o capturar precios, condiciones y datos de cada propuesta.',
                    'Cuando se prepara la informacion para evaluar o seleccionar la mejor alternativa.',
                ],
                steps: [
                    'Ingresa desde el requerimiento correspondiente para mantener la trazabilidad.',
                    'Registra el proveedor, moneda, fechas, condiciones y observaciones de la cotizacion.',
                    'Completa los precios por item y valida que las cantidades correspondan al requerimiento.',
                    'Adjunta o revisa el PDF cuando el flujo lo permita.',
                    'Guarda la cotizacion y revisa el comparativo antes de seleccionar ganador.',
                ],
                controls: [
                    'Formulario de cotizacion: captura proveedor, importes, condiciones y estado.',
                    'Items cotizados: permite completar precios y observaciones por producto o servicio.',
                    'Vista PDF: abre la cotizacion en modal cuando hay documento disponible.',
                    'Acciones de fila: editar, eliminar o revisar detalle segun permisos.',
                ],
                rules: [
                    'La cotizacion debe estar asociada al requerimiento correcto.',
                    'Los precios deben registrarse con la moneda y condiciones reales de la oferta.',
                    'No selecciones ganador sin validar consistencia entre items, totales y documentos.',
                ],
                tips: [
                    'Mantén nombres y condiciones claros para que el comparativo sea confiable.',
                    'Si una oferta no cubre todos los items, deja constancia en observaciones.',
                ],
                permissions: ['purchases.ver', 'purchases.editar', 'purchases.aprobar'],
            ),
            'comparison' => $this->manual(
                title: 'Manual de usuario - Comparacion y seleccion',
                module: 'Compras / Comparativo',
                purpose: 'Evaluar cotizaciones recibidas, revisar diferencias economicas y seleccionar la propuesta ganadora.',
                when: [
                    'Cuando hay dos o mas cotizaciones para un requerimiento.',
                    'Cuando necesitas justificar la seleccion de proveedor.',
                    'Cuando se requiere emitir una orden de compra a partir de la cotizacion elegida.',
                ],
                steps: [
                    'Revisa el resumen del requerimiento y verifica que las cotizaciones correspondan al mismo alcance.',
                    'Compara precios, condiciones, puntajes y observaciones por proveedor.',
                    'Abre la vista completa o PDF si necesitas validar evidencia documental.',
                    'Selecciona la cotizacion ganadora cuando el analisis este completo.',
                    'Genera o continua hacia la orden de compra segun el estado del flujo.',
                ],
                controls: [
                    'Tabla comparativa: muestra diferencias entre proveedores e items.',
                    'Vista completa: expande el comparativo para lectura detallada.',
                    'Seleccionar ganador: confirma la cotizacion elegida.',
                    'Exportar PDF: genera soporte formal del analisis.',
                ],
                rules: [
                    'La seleccion de ganador requiere permiso de aprobacion.',
                    'El comparativo depende de cotizaciones correctamente registradas.',
                    'Una vez generada la orden, evita cambios sin revisar impacto documental.',
                ],
                tips: [
                    'Documenta observaciones si la opcion elegida no es la de menor precio.',
                    'Valida condiciones de entrega y pago antes de confirmar ganador.',
                ],
                permissions: ['purchases.ver', 'purchases.aprobar', 'purchases.exportar'],
            ),
            'purchase_orders' => $this->manual(
                title: 'Manual de usuario - Ordenes de compra',
                module: 'Compras / Ordenes de compra',
                purpose: 'Gestionar ordenes emitidas a proveedores, revisar su detalle, registrar conformidad y emitir documentos PDF.',
                when: [
                    'Cuando una cotizacion ganadora debe formalizarse como orden de compra.',
                    'Cuando necesitas validar estado, proveedor, importes y documentos de una OC.',
                    'Cuando corresponde registrar conformidad, rechazo o anulacion.',
                ],
                steps: [
                    'Filtra ordenes por busqueda, estado u obra para ubicar la OC.',
                    'Abre el detalle para revisar proveedor, items, totales, trazabilidad y documentos.',
                    'Previsualiza o descarga el PDF cuando necesites soporte formal.',
                    'Registra conformidad si los bienes o servicios fueron aceptados.',
                    'Anula o rechaza solo cuando exista sustento operativo.',
                ],
                controls: [
                    'Filtros: reducen la lista de OC por criterio operativo.',
                    'Detalle: concentra datos de la orden, items y trazabilidad.',
                    'Vista PDF: muestra la orden en modal antes de descargar.',
                    'Conformidad: registra resultado y observacion de recepcion.',
                ],
                rules: [
                    'La conformidad habilita procesos posteriores como cuentas por pagar.',
                    'Las anulaciones deben ser excepcionales y coherentes con el estado actual.',
                    'El PDF usa la configuracion de formatos PDF de la empresa activa.',
                ],
                tips: [
                    'Revisa totales y proveedor antes de emitir o compartir el PDF.',
                    'Usa observaciones claras al registrar conformidad o rechazo.',
                ],
                permissions: ['purchases.ver', 'purchases.exportar', 'purchases.aprobar'],
            ),
            'accounts_payable' => $this->manual(
                title: 'Manual de usuario - Cuentas por pagar',
                module: 'Cuentas por pagar',
                purpose: 'Controlar documentos de pago derivados de ordenes conformes y registrar pagos asociados.',
                when: [
                    'Cuando una orden conforme genera obligaciones por pagar.',
                    'Cuando necesitas subir facturas, recibos u otros documentos sustentatorios.',
                    'Cuando se registran pagos parciales o totales a proveedores.',
                ],
                steps: [
                    'Ubica la cuenta por pagar desde el listado usando filtros o busqueda.',
                    'Abre el detalle para revisar proveedor, orden, importes, documentos y pagos.',
                    'Sube documentos sustentatorios segun el tipo requerido.',
                    'Registra pagos indicando fecha, monto, cuenta bancaria y observacion.',
                    'Verifica que el estado refleje si la cuenta esta pendiente, parcial o pagada.',
                ],
                controls: [
                    'Listado: muestra cuentas con estado, proveedor, vencimiento e importe.',
                    'Detalle: concentra documentos, pagos y saldo.',
                    'Registrar pago: abre modal para ingresar desembolsos.',
                    'Vista PDF: permite revisar documentos asociados cuando estan disponibles.',
                ],
                rules: [
                    'Los pagos deben corresponder a documentos y saldos reales.',
                    'La cuenta bancaria seleccionada debe representar el movimiento financiero correcto.',
                    'El estado cambia segun documentos, pagos y saldo registrado.',
                ],
                tips: [
                    'Antes de pagar, confirma que el documento sustentatorio sea legible y correcto.',
                    'Registra pagos parciales con observaciones para evitar conciliaciones confusas.',
                ],
                permissions: ['payments.ver', 'payments.crear', 'cuentas_pagar.pagar'],
            ),
            'documents' => $this->manual(
                title: 'Manual de usuario - Documentos',
                module: 'Documentos',
                purpose: 'Gestionar documentos internos con bandejas, trazabilidad, movimientos, aprobaciones y consulta por obra.',
                when: [
                    'Cuando necesitas crear o recibir documentos vinculados a la operacion.',
                    'Cuando debes revisar documentos en bandeja de entrada o salida.',
                    'Cuando se requiere ver historial, movimientos o linea de tiempo de un documento.',
                ],
                steps: [
                    'Ingresa a Bandeja de entrada para revisar documentos pendientes o recibidos.',
                    'Crea un documento indicando tipo, prioridad, asunto, obra y destinatarios cuando corresponda.',
                    'Usa Bandeja de salida para dar seguimiento a documentos enviados.',
                    'Consulta Documentos por obra para agrupar el repositorio operativo.',
                    'Abre el detalle o linea de tiempo para revisar acciones, observaciones y cambios de estado.',
                ],
                controls: [
                    'Crear documento: inicia un registro documental.',
                    'Bandejas: separan documentos recibidos, enviados y agrupados por obra.',
                    'Detalle: muestra datos, adjuntos, movimientos y acciones disponibles.',
                    'Linea de tiempo: permite auditar el recorrido del documento.',
                ],
                rules: [
                    'Las acciones disponibles dependen del estado y del permiso del usuario.',
                    'Los documentos deben mantener asunto, prioridad y obra correctos para facilitar busqueda.',
                    'La trazabilidad se construye con cada movimiento registrado.',
                ],
                tips: [
                    'Usa asuntos descriptivos y evita duplicar documentos con el mismo objetivo.',
                    'Revisa la linea de tiempo antes de preguntar por el estado de un documento.',
                ],
                permissions: ['documents.ver', 'documents.crear', 'documents.editar', 'documents.aprobar'],
            ),
            'projects' => $this->manual(
                title: 'Manual de usuario - Obras',
                module: 'Obras',
                purpose: 'Administrar obras, clientes, ubicaciones, presupuestos y estado operativo dentro de la empresa activa.',
                when: [
                    'Cuando se inicia una nueva obra o proyecto operativo.',
                    'Cuando necesitas actualizar datos de cliente, ciudad, presupuesto o estado.',
                    'Cuando otros modulos requieren seleccionar una obra confiable.',
                ],
                steps: [
                    'Busca la obra por codigo, nombre, cliente, ciudad o estado.',
                    'Presiona Nueva obra para registrar informacion general y financiera.',
                    'Guarda el registro y valida el codigo operativo asignado.',
                    'Abre el detalle para revisar datos de la obra y relaciones principales.',
                    'Actualiza el estado cuando la obra avance de planificada a activa, pausada o cerrada.',
                ],
                controls: [
                    'Filtros y busqueda: ubican obras por criterios operativos.',
                    'Nueva obra: abre el formulario de alta.',
                    'Detalle: permite consultar informacion completa sin editar.',
                    'Acciones: editar o eliminar segun permisos y reglas del negocio.',
                ],
                rules: [
                    'Cada obra pertenece a la empresa activa.',
                    'El codigo operativo puede generarse con correlativos configurados.',
                    'No elimines obras con informacion relacionada sin validar impacto.',
                ],
                tips: [
                    'Mantén ciudad y cliente consistentes para que los reportes ejecutivos sean utiles.',
                    'Actualiza estados para no inflar indicadores de obras activas.',
                ],
                permissions: ['projects.ver', 'projects.crear', 'projects.editar'],
            ),
            'suppliers' => $this->manual(
                title: 'Manual de usuario - Proveedores',
                module: 'Proveedores',
                purpose: 'Mantener el directorio de proveedores usado por compras, contratos, pagos y cuentas por pagar.',
                when: [
                    'Cuando se incorpora un nuevo proveedor al proceso de compras.',
                    'Cuando necesitas actualizar RUC, razon social, contacto, ciudad o estado.',
                    'Cuando se requiere revisar detalle antes de cotizar, contratar o pagar.',
                ],
                steps: [
                    'Filtra por busqueda, ciudad o estado para verificar si el proveedor ya existe.',
                    'Crea el proveedor con datos tributarios, comerciales y de contacto.',
                    'Actualiza el estado para distinguir proveedores activos e inactivos.',
                    'Consulta el detalle antes de usarlo en cotizaciones o contratos.',
                    'Corrige informacion de contacto cuando cambie el responsable o correo.',
                ],
                controls: [
                    'Buscar: localiza por razon social, documento o contacto.',
                    'Filtros: acotan por ciudad y estado.',
                    'Nuevo proveedor: abre el formulario de registro.',
                    'Detalle: muestra informacion comercial y operativa.',
                ],
                rules: [
                    'Evita duplicar proveedores con el mismo documento.',
                    'Solo proveedores activos deberian usarse en nuevos flujos.',
                    'La calidad de datos impacta documentos PDF y comunicaciones.',
                ],
                tips: [
                    'Valida razon social y documento antes de guardar.',
                    'Usa observaciones para advertencias comerciales relevantes.',
                ],
                permissions: ['suppliers.ver', 'suppliers.crear', 'suppliers.editar'],
            ),
            'contracts' => $this->manual(
                title: 'Manual de usuario - Contratos',
                module: 'Contratos',
                purpose: 'Controlar contratos con proveedores, vigencias, montos, estados, anexos y cronogramas de pago.',
                when: [
                    'Cuando una orden o negociacion debe formalizarse en un contrato.',
                    'Cuando necesitas revisar vigencias, saldos y documentos contractuales.',
                    'Cuando se planifican pagos por cronograma.',
                ],
                steps: [
                    'Ubica contratos por proveedor, obra, estado o busqueda.',
                    'Abre el detalle para revisar informacion contractual, montos, fechas y anexos.',
                    'Genera o revisa el PDF contractual cuando corresponda.',
                    'Accede al cronograma de pagos desde el contrato si necesita cuotas programadas.',
                    'Actualiza estados de aprobacion, ejecucion, cancelacion o cierre segun el flujo.',
                ],
                controls: [
                    'Listado: resume proveedor, obra, estado y montos.',
                    'Detalle: muestra contrato completo y acciones relacionadas.',
                    'PDF: previsualiza o descarga el documento contractual.',
                    'Cronograma: administra pagos programados asociados.',
                ],
                rules: [
                    'Los contratos deben mantener proveedor, obra, moneda y montos coherentes.',
                    'Las aprobaciones y cancelaciones requieren permisos especificos.',
                    'Los pagos deben respetar el cronograma y el saldo contractual.',
                ],
                tips: [
                    'Revisa fechas de vigencia para evitar contratos vencidos sin cierre.',
                    'Usa el detalle antes de registrar pagos asociados.',
                ],
                permissions: ['contracts.ver', 'contracts.aprobar', 'contracts.exportar'],
            ),
            'payments' => $this->manual(
                title: 'Manual de usuario - Pagos a proveedores',
                module: 'Pagos a proveedores',
                purpose: 'Registrar pagos relacionados a contratos y mantener trazabilidad financiera por proveedor.',
                when: [
                    'Cuando se realiza un desembolso a proveedor por contrato.',
                    'Cuando necesitas revisar pagos registrados por fecha, proveedor o contrato.',
                    'Cuando se controla el saldo pendiente de compromisos contractuales.',
                ],
                steps: [
                    'Filtra pagos existentes para evitar registros duplicados.',
                    'Presiona Nuevo pago y selecciona contrato, proveedor, monto, fecha y medio de pago.',
                    'Registra observaciones y documentos si el formulario lo solicita.',
                    'Guarda y verifica que el saldo asociado se actualice.',
                    'Usa cronogramas de pago cuando el contrato tenga cuotas programadas.',
                ],
                controls: [
                    'Filtros: permiten ubicar pagos por contexto financiero.',
                    'Nuevo pago: abre el formulario de desembolso.',
                    'Cronograma: gestiona cuotas relacionadas a un contrato.',
                    'Acciones: editar o eliminar cuando el rol y estado lo permitan.',
                ],
                rules: [
                    'El monto pagado no debe exceder el saldo real del contrato.',
                    'La fecha y cuenta deben coincidir con la operacion financiera.',
                    'Los pagos afectan reportes ejecutivos y saldos pendientes.',
                ],
                tips: [
                    'Revisa banco y contrato antes de guardar.',
                    'Registra observaciones cuando el pago sea parcial o excepcional.',
                ],
                permissions: ['payments.ver', 'payments.crear', 'payments.editar'],
            ),
            'banks' => $this->manual(
                title: 'Manual de usuario - Bancos',
                module: 'Bancos',
                purpose: 'Administrar cuentas bancarias, caja y movimientos manuales de entrada o salida de dinero.',
                when: [
                    'Cuando se crea o actualiza una cuenta bancaria de la empresa.',
                    'Cuando se registra un movimiento financiero manual.',
                    'Cuando se consulta saldo o trazabilidad de movimientos.',
                ],
                steps: [
                    'Revisa las cuentas existentes y sus saldos antes de crear una nueva.',
                    'Crea o edita la cuenta con banco, moneda, numero y estado.',
                    'Registra movimientos manuales indicando tipo, monto, fecha y observacion.',
                    'Valida que los movimientos impacten el saldo esperado.',
                    'Mantén inactivas las cuentas que ya no se usen.',
                ],
                controls: [
                    'Nueva cuenta: registra informacion bancaria.',
                    'Movimiento: abre modal para ingreso o salida manual.',
                    'Tabla de cuentas: resume saldo y estado.',
                    'Acciones: editar cuenta o registrar movimiento.',
                ],
                rules: [
                    'Los movimientos manuales deben tener sustento contable.',
                    'La moneda de la cuenta debe coincidir con la operacion registrada.',
                    'No uses cuentas inactivas para nuevos pagos.',
                ],
                tips: [
                    'Registra observaciones claras para conciliacion posterior.',
                    'Verifica el signo del movimiento antes de confirmar.',
                ],
                permissions: ['bancos.ver', 'bancos.crear', 'bancos.editar'],
            ),
            'warehouse' => $this->manual(
                title: 'Manual de usuario - Almacen',
                module: 'Almacen',
                purpose: 'Controlar inventario, stock por obra, salidas manuales, transferencias y kardex de materiales o servicios.',
                when: [
                    'Cuando una orden conforme ingresa materiales al almacen.',
                    'Cuando se necesita retirar stock para consumo de obra.',
                    'Cuando se transfiere stock entre obras o se revisa kardex.',
                ],
                steps: [
                    'Filtra el inventario por busqueda, obra, estado o tipo de item.',
                    'Revisa cantidad disponible antes de registrar salida o transferencia.',
                    'Usa salida manual para descontar consumo con fecha y observacion.',
                    'Usa transferencia para mover stock a otra obra.',
                    'Abre kardex para auditar entradas, salidas, transferencias y origen del movimiento.',
                ],
                controls: [
                    'Filtros: localizan stock por obra, producto, estado o tipo.',
                    'Salida manual: descuenta unidades disponibles.',
                    'Transferir: mueve stock entre obras.',
                    'Kardex: muestra historial completo del item.',
                ],
                rules: [
                    'No se debe retirar ni transferir mas cantidad que el stock disponible.',
                    'Toda salida o transferencia debe tener motivo operativo.',
                    'Los movimientos quedan trazados por usuario, fecha y origen.',
                ],
                tips: [
                    'Consulta kardex antes de corregir diferencias de stock.',
                    'Usa observaciones precisas para consumos no asociados a una orden.',
                ],
                permissions: ['almacen.ver', 'almacen.mover', 'almacen.transferir'],
            ),
            'mechanics_dashboard' => $this->manual(
                title: 'Manual de usuario - Panel de mecanica',
                module: 'Mecanica / Panel',
                purpose: 'Visualizar indicadores, costos, disponibilidad y actividad del modulo de mecanica.',
                when: [
                    'Cuando necesitas una vista rapida de maquinaria, mantenimientos y costos.',
                    'Cuando se evalua carga operativa del taller o equipos por obra.',
                    'Cuando se preparan decisiones de mantenimiento preventivo o correctivo.',
                ],
                steps: [
                    'Revisa los KPI principales del panel.',
                    'Analiza graficos de estados, costos o actividad segun la informacion disponible.',
                    'Usa los accesos del menu de mecanica para profundizar en equipos, revisiones, mantenimientos y repuestos.',
                    'Contrasta indicadores con reportes exportables cuando necesites respaldo.',
                ],
                controls: [
                    'KPI: resumen de maquinaria, mantenimiento y costos.',
                    'Graficos: distribucion y tendencias operativas.',
                    'Menu de mecanica: navega a submodulos especializados.',
                ],
                rules: [
                    'Los indicadores dependen de registros completos en equipos, OT, revisiones y repuestos.',
                    'El alcance esta limitado por empresa activa y permisos.',
                ],
                tips: [
                    'Si un costo no aparece, verifica que la OT o movimiento tenga importe registrado.',
                    'Usa el panel como alerta inicial y los submodulos para accion operativa.',
                ],
                permissions: ['mecanica.ver'],
            ),
            'mechanics_reports' => $this->manual(
                title: 'Manual de usuario - Reportes de mecanica',
                module: 'Mecanica / Reportes',
                purpose: 'Generar reportes PDF y Excel sobre equipos, revisiones, mantenimientos, ordenes de trabajo, costos y repuestos.',
                when: [
                    'Cuando se necesita entregar informacion tecnica o gerencial fuera del sistema.',
                    'Cuando se revisan vencimientos, consumo de repuestos o costos de mantenimiento.',
                    'Cuando se requiere analizar mecanica por obra, equipo o tecnico.',
                ],
                steps: [
                    'Selecciona el reporte que corresponde a la necesidad operativa.',
                    'Aplica filtros disponibles como fechas, obra, equipo o estado.',
                    'Genera PDF para presentacion formal o Excel para analisis.',
                    'Revisa la vista previa cuando el sistema la ofrezca.',
                    'Comparte el archivo solo despues de validar alcance y periodo.',
                ],
                controls: [
                    'Filtros de reporte: determinan periodo y alcance.',
                    'Exportar PDF: genera documento formal.',
                    'Exportar Excel: genera archivo editable para analisis.',
                    'Vista previa: permite revisar antes de descargar.',
                ],
                rules: [
                    'Exportar requiere permisos de mecanica o del submodulo correspondiente.',
                    'Los reportes reflejan informacion registrada hasta el momento de generacion.',
                    'Los PDF usan el formato corporativo configurado.',
                ],
                tips: [
                    'Para analisis de detalle usa Excel; para entrega formal usa PDF.',
                    'Verifica filtros de fechas para evitar reportes incompletos.',
                ],
                permissions: ['mecanica.ver', 'mecanica.exportar'],
            ),
            'mechanics_equipment' => $this->manual(
                title: 'Manual de usuario - Equipos y tipos de equipo',
                module: 'Mecanica / Equipos',
                purpose: 'Registrar maquinaria, clasificarla por tipo, controlar estado operacional y consultar su informacion tecnica.',
                when: [
                    'Cuando ingresa un nuevo equipo o maquinaria al control de la empresa.',
                    'Cuando se actualizan datos tecnicos, obra asignada o estado operacional.',
                    'Cuando se necesita clasificar equipos para reportes y mantenimientos.',
                ],
                steps: [
                    'Crea o actualiza tipos de equipo antes de registrar maquinaria si falta la clasificacion.',
                    'Registra el equipo con codigo interno, tipo, obra, placa o datos tecnicos.',
                    'Actualiza estado operacional segun disponibilidad real.',
                    'Abre el detalle para revisar informacion y actividad asociada.',
                    'Exporta o consulta reportes cuando necesites inventario tecnico.',
                ],
                controls: [
                    'Nuevo equipo: abre formulario de maquinaria.',
                    'Tipos de equipo: administra clasificaciones reutilizables.',
                    'Detalle: muestra informacion tecnica y operativa.',
                    'Filtros: ubican equipos por obra, tipo, estado o busqueda.',
                ],
                rules: [
                    'Cada equipo debe tener identificacion interna clara.',
                    'El tipo de equipo mejora reportes y programacion de mantenimiento.',
                    'El estado operacional debe reflejar la situacion real en campo.',
                ],
                tips: [
                    'Evita codigos duplicados o ambiguos.',
                    'Actualiza obra asignada cuando el equipo se traslade.',
                ],
                permissions: ['equipos.ver', 'equipos.crear', 'equipos.editar'],
            ),
            'mechanics_inspections' => $this->manual(
                title: 'Manual de usuario - Revisiones tecnicas',
                module: 'Mecanica / Revisiones tecnicas',
                purpose: 'Programar y controlar inspecciones tecnicas de equipos, incluyendo vencimientos y resultados.',
                when: [
                    'Cuando un equipo requiere revision periodica o certificacion.',
                    'Cuando se necesita controlar revisiones vencidas o proximas a vencer.',
                    'Cuando se registran resultados de inspeccion tecnica.',
                ],
                steps: [
                    'Filtra por equipo, obra, estado o fecha para identificar revisiones pendientes.',
                    'Crea una revision indicando equipo, fecha programada, responsable y observaciones.',
                    'Actualiza resultado o estado cuando se ejecute la inspeccion.',
                    'Revisa vencimientos y prioriza equipos criticos.',
                    'Exporta reportes si necesitas entregar control de revisiones.',
                ],
                controls: [
                    'Nueva revision: registra programacion o resultado.',
                    'Filtros: ubican revisiones por estado, equipo u obra.',
                    'Estados: indican pendiente, vigente, vencida o completada segun el flujo.',
                    'Exportar: genera soporte cuando el usuario tiene permiso.',
                ],
                rules: [
                    'Las fechas deben registrarse con precision para alertas y reportes.',
                    'Una revision vencida debe atenderse antes de operar equipos criticos.',
                    'Los resultados deben ser trazables y claros.',
                ],
                tips: [
                    'Usa observaciones para hallazgos tecnicos relevantes.',
                    'Revisa vencimientos al inicio de la semana operativa.',
                ],
                permissions: ['revisiones.ver', 'revisiones.crear', 'revisiones.exportar'],
            ),
            'mechanics_maintenance' => $this->manual(
                title: 'Manual de usuario - Mantenimientos',
                module: 'Mecanica / Mantenimiento preventivo y correctivo',
                purpose: 'Gestionar planes preventivos e intervenciones correctivas por equipo, fecha, costo y estado.',
                when: [
                    'Cuando se programa mantenimiento preventivo.',
                    'Cuando un equipo presenta falla y requiere mantenimiento correctivo.',
                    'Cuando se controlan costos, responsables y cierre de intervenciones.',
                ],
                steps: [
                    'Filtra registros por equipo, obra, estado o rango de fechas.',
                    'Crea el mantenimiento con equipo, fecha, responsable, descripcion y costo estimado si aplica.',
                    'Actualiza estado durante la ejecucion.',
                    'Registra cierre, observaciones y costos reales cuando termine la intervencion.',
                    'Consulta reportes para analizar costos o recurrencias.',
                ],
                controls: [
                    'Nuevo mantenimiento: abre formulario preventivo o correctivo.',
                    'Filtros: reducen la lista por contexto tecnico.',
                    'Estados: reflejan programado, en proceso, finalizado o cancelado.',
                    'Acciones: editar o cerrar segun permisos.',
                ],
                rules: [
                    'Todo mantenimiento debe estar asociado a un equipo.',
                    'Los costos alimentan reportes de mecanica.',
                    'El cierre debe reflejar que la intervencion realmente termino.',
                ],
                tips: [
                    'Describe la causa en correctivos para detectar fallas recurrentes.',
                    'Programa preventivos con anticipacion para reducir paradas no planificadas.',
                ],
                permissions: ['mantenimientos.ver', 'mantenimientos.crear', 'mantenimientos.cerrar'],
            ),
            'mechanics_work_orders' => $this->manual(
                title: 'Manual de usuario - Ordenes de trabajo',
                module: 'Mecanica / Ordenes de trabajo',
                purpose: 'Planificar, asignar, visualizar y cerrar trabajos de mecanica mediante graficos, kanban, lista, recursos y calendario.',
                when: [
                    'Cuando un equipo requiere una actividad tecnica asignable a un responsable.',
                    'Cuando se necesita controlar carga de tecnicos, vencimientos y costos.',
                    'Cuando el equipo de mecanica trabaja con tablero kanban o calendario.',
                ],
                steps: [
                    'Usa filtros principales y avanzados para acotar obra, equipo, tecnico, estado, fechas, tipo o prioridad.',
                    'Cambia entre Graficos, Kanban, Lista, Recursos y Calendario segun la forma de trabajo.',
                    'Crea una nueva OT con equipo, tipo, prioridad, fecha programada, responsable y descripcion.',
                    'En Kanban, mueve tarjetas entre columnas si tienes permiso para cambiar estado.',
                    'Asigna tecnico, registra avance, costos y cierre cuando corresponda.',
                ],
                controls: [
                    'Mas filtros: muestra criterios avanzados como fechas, prioridad y vencidas.',
                    'Pestanas: cambian la representacion de las OT sin perder el contexto.',
                    'Kanban: permite mover OT entre estados.',
                    'Asignar: vincula un responsable tecnico.',
                    'Nueva OT: abre el formulario principal.',
                ],
                rules: [
                    'Mover tarjetas cambia el estado de la OT.',
                    'Las OT vencidas dependen de la fecha programada y estado actual.',
                    'Los costos y tiempos registrados impactan reportes del modulo.',
                ],
                tips: [
                    'Usa Kanban para seguimiento diario y Lista para revision detallada.',
                    'Filtra por tecnico para balancear carga antes de asignar nuevas OT.',
                ],
                permissions: ['mantenimientos.ver', 'mantenimientos.crear', 'mantenimientos.cerrar'],
            ),
            'mechanics_spare_parts' => $this->manual(
                title: 'Manual de usuario - Repuestos',
                module: 'Mecanica / Repuestos',
                purpose: 'Controlar repuestos, stock, movimientos y consumo asociado a mantenimiento.',
                when: [
                    'Cuando se registra un repuesto disponible para mecanica.',
                    'Cuando se ingresa o retira stock de repuestos.',
                    'Cuando se revisa consumo por equipo, OT o mantenimiento.',
                ],
                steps: [
                    'Busca el repuesto por nombre, codigo, estado o stock.',
                    'Crea o edita el repuesto con datos de identificacion, unidad, stock minimo y estado.',
                    'Registra movimientos de entrada o salida indicando cantidad y motivo.',
                    'Verifica que el stock resultante sea correcto.',
                    'Usa reportes de repuestos consumidos para analizar uso y reposicion.',
                ],
                controls: [
                    'Nuevo repuesto: abre formulario de parte.',
                    'Movimiento: registra entrada o salida.',
                    'Tabla: muestra stock, estado y datos principales.',
                    'Filtros: ayudan a localizar repuestos criticos.',
                ],
                rules: [
                    'No registres salidas mayores al stock disponible.',
                    'Todo movimiento debe tener motivo claro.',
                    'El stock minimo sirve para alertar reposicion.',
                ],
                tips: [
                    'Mantén nombres normalizados para evitar repuestos duplicados.',
                    'Revisa consumos frecuentes para planificar compras.',
                ],
                permissions: ['mecanica.ver'],
            ),
            'security' => $this->manual(
                title: 'Manual de usuario - Seguridad',
                module: 'Seguridad',
                purpose: 'Administrar empresas, usuarios, roles, permisos y auditoria de acciones del sistema.',
                when: [
                    'Cuando se crea una empresa o se actualiza su informacion.',
                    'Cuando se da acceso a usuarios o se ajustan roles.',
                    'Cuando se revisan permisos disponibles o auditoria de actividad.',
                ],
                steps: [
                    'Gestiona empresas antes de asignar usuarios a ellas.',
                    'Crea usuarios y vincula empresas, rol y estado de acceso.',
                    'Edita roles para ajustar permisos por perfil operativo.',
                    'Consulta permisos para entender que habilita cada accion.',
                    'Revisa auditoria para rastrear cambios y acciones relevantes.',
                ],
                controls: [
                    'Empresas: administra entidades y branding.',
                    'Usuarios: controla accesos, empresas y roles.',
                    'Roles: agrupa permisos por perfil.',
                    'Permisos: muestra catalogo de capacidades.',
                    'Auditoria: registra trazabilidad por usuario y empresa.',
                ],
                rules: [
                    'Los permisos deben asignarse con minimo privilegio necesario.',
                    'Los usuarios operan dentro de la empresa activa.',
                    'La auditoria es de consulta y no debe alterarse manualmente.',
                ],
                tips: [
                    'Revisa permisos antes de crear roles nuevos.',
                    'Desactiva accesos que ya no correspondan en vez de compartir cuentas.',
                ],
                permissions: ['companies.ver', 'users.ver', 'roles.ver', 'permissions.ver', 'audits.ver'],
            ),
            'settings' => $this->manual(
                title: 'Manual de usuario - Configuracion',
                module: 'Configuracion',
                purpose: 'Administrar catalogos, correlativos, formatos PDF y tipos de costo que soportan la operacion del sistema.',
                when: [
                    'Cuando se necesitan valores maestros para formularios y filtros.',
                    'Cuando se configura la numeracion automatica por modulo y anio.',
                    'Cuando se ajusta branding, membrete, logo, colores o pie de pagina de PDF.',
                    'Cuando se definen tipos de costo para requerimientos y reportes.',
                ],
                steps: [
                    'Usa Catalogos para mantener ciudades, bancos, monedas, metodos de pago y datos base.',
                    'Configura Correlativos antes de emitir documentos con codigo automatico.',
                    'Ajusta Formatos PDF y previsualiza antes de guardar cambios corporativos.',
                    'Gestiona Tipos de costo para clasificar gastos de requerimientos.',
                    'Valida que los cambios no rompan nomenclaturas usadas por operacion.',
                ],
                controls: [
                    'Pestanas de catalogos: separan familias de datos maestros.',
                    'Formulario de correlativos: define prefijos, secuencia y formato.',
                    'Vista previa PDF: permite revisar branding antes de usarlo en reportes.',
                    'Tipos de costo: crea, edita y ordena clasificaciones.',
                ],
                rules: [
                    'Cambios en catalogos afectan formularios de varios modulos.',
                    'Correlativos mal configurados pueden producir codigos confusos.',
                    'Formatos PDF aplican a documentos generados por empresa.',
                ],
                tips: [
                    'Realiza cambios de configuracion en horarios controlados.',
                    'Prueba previsualizacion PDF antes de entregar documentos formales.',
                ],
                permissions: ['catalogs.ver', 'pdf-formats.ver'],
            ),
            'site_admin' => $this->manual(
                title: 'Manual de usuario - Administracion del sitio web',
                module: 'Sitio web',
                purpose: 'Gestionar contenido publico, proyectos del portafolio y mensajes recibidos desde el formulario de contacto.',
                when: [
                    'Cuando se actualizan textos, imagenes o secciones del sitio publico.',
                    'Cuando se publican o editan proyectos mostrados en el portafolio.',
                    'Cuando se revisan mensajes enviados por visitantes.',
                ],
                steps: [
                    'Edita contenido del sitio cuidando tono, ortografia e imagenes.',
                    'Gestiona proyectos del portafolio con titulo, descripcion, imagen y estado de publicacion.',
                    'Revisa mensajes de contacto y marca seguimiento cuando corresponda.',
                    'Valida en la pagina publica que el contenido se vea correctamente.',
                ],
                controls: [
                    'Contenido: administra bloques y textos del sitio.',
                    'Portafolio: crea o edita proyectos publicados.',
                    'Mensajes: abre detalle de comunicaciones recibidas.',
                ],
                rules: [
                    'Este modulo esta pensado para usuarios administradores.',
                    'Las imagenes y textos impactan directamente la presencia publica.',
                    'Los mensajes de contacto pueden contener datos personales y deben tratarse con cuidado.',
                ],
                tips: [
                    'Revisa vista publica despues de cambios importantes.',
                    'Mantén proyectos publicados con imagenes actualizadas y descripciones claras.',
                ],
                permissions: ['Super Admin'],
            ),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function coveredRouteNames(): array
    {
        return array_keys($this->routeMap());
    }

    /**
     * @return array<string, string>
     */
    protected function routeMap(): array
    {
        return [
            'dashboard' => 'dashboard',
            'modules.purchases' => 'purchases',
            'purchases.send-suppliers' => 'purchases',
            'purchases.quotations' => 'quotations',
            'purchases.comparison' => 'comparison',
            'purchases.winner' => 'comparison',
            'purchases.orders' => 'purchase_orders',
            'accounts-payable.index' => 'accounts_payable',
            'accounts-payable.show' => 'accounts_payable',
            'modules.documents' => 'documents',
            'documents.outbox' => 'documents',
            'documents.projects' => 'documents',
            'documents.show' => 'documents',
            'documents.timeline' => 'documents',
            'modules.projects' => 'projects',
            'modules.suppliers' => 'suppliers',
            'modules.contracts' => 'contracts',
            'payments.schedules' => 'contracts',
            'modules.payments' => 'payments',
            'modules.banks' => 'banks',
            'modules.warehouse' => 'warehouse',
            'modules.mechanics' => 'mechanics_dashboard',
            'mechanics.reports' => 'mechanics_reports',
            'mechanics.equipments' => 'mechanics_equipment',
            'mechanics.equipment-types' => 'mechanics_equipment',
            'mechanics.inspections' => 'mechanics_inspections',
            'mechanics.preventive' => 'mechanics_maintenance',
            'mechanics.corrective' => 'mechanics_maintenance',
            'mechanics.work-orders' => 'mechanics_work_orders',
            'mechanics.spare-parts' => 'mechanics_spare_parts',
            'companies.index' => 'security',
            'companies.create' => 'security',
            'companies.edit' => 'security',
            'users.index' => 'security',
            'users.create' => 'security',
            'users.edit' => 'security',
            'security.roles' => 'security',
            'security.permissions' => 'security',
            'audits.users' => 'security',
            'settings.catalogs' => 'settings',
            'settings.correlatives' => 'settings',
            'settings.pdf-formats' => 'settings',
            'settings.cost-types' => 'settings',
            'admin.site-content' => 'site_admin',
            'admin.showcase-projects' => 'site_admin',
            'admin.contact-messages' => 'site_admin',
        ];
    }

    protected function fallbackKey(string $routeName): ?string
    {
        return match (true) {
            str_starts_with($routeName, 'purchases.quotations') => 'quotations',
            str_starts_with($routeName, 'purchases.comparison'),
            str_starts_with($routeName, 'purchases.winner') => 'comparison',
            str_starts_with($routeName, 'purchases.orders') => 'purchase_orders',
            str_starts_with($routeName, 'accounts-payable.') => 'accounts_payable',
            str_starts_with($routeName, 'documents.') => 'documents',
            str_starts_with($routeName, 'mechanics.report.') => 'mechanics_reports',
            str_starts_with($routeName, 'mechanics.') => 'mechanics_dashboard',
            str_starts_with($routeName, 'companies.'),
            str_starts_with($routeName, 'users.'),
            str_starts_with($routeName, 'security.'),
            str_starts_with($routeName, 'audits.') => 'security',
            str_starts_with($routeName, 'settings.') => 'settings',
            str_starts_with($routeName, 'admin.') => 'site_admin',
            default => null,
        };
    }

    /**
     * @param  array<int, string>  $when
     * @param  array<int, string>  $steps
     * @param  array<int, string>  $controls
     * @param  array<int, string>  $rules
     * @param  array<int, string>  $tips
     * @param  array<int, string>  $permissions
     * @return array<string, mixed>
     */
    protected function manual(
        string $title,
        string $module,
        string $purpose,
        array $when,
        array $steps,
        array $controls,
        array $rules,
        array $tips,
        array $permissions,
    ): array {
        return [
            'title' => $title,
            'module' => $module,
            'purpose' => $purpose,
            'sections' => [
                ['title' => 'Cuando usarlo', 'items' => $when],
                ['title' => 'Flujo recomendado', 'items' => $steps, 'ordered' => true],
                ['title' => 'Controles principales', 'items' => $controls],
                ['title' => 'Reglas importantes', 'items' => $rules],
                ['title' => 'Recomendaciones', 'items' => $tips],
            ],
            'permissions' => Arr::map($permissions, fn (string $permission): string => $permission),
        ];
    }
}
