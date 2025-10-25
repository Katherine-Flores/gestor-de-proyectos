@extends('layouts.app')

@section('title', 'Reportes de Proyectos')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Generador de Reportes</h1>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="report-type" class="form-label">Seleccionar Tipo de Reporte</label>
                    <select class="form-select" id="report-type">
                        <option value="proyectos">Proyectos por Rango de Fecha</option>
                        <option value="en-ejecucion">Proyectos en Ejecución</option>
                        <option value="finalizados">Proyectos Finalizados/Cancelados</option>
                        <option value="lideres">Proyectos por Líder</option>
                        <option value="clientes">Proyectos por Cliente</option>
                    </select>
                </div>

                <div class="col-md-5 row" id="date-range-controls" style="display:none;">
                    <div class="col-4">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo">
                            <option value="">(Detalle - Por Proyecto)</option>
                            <option value="mensual">Agregado Mensual</option>
                            <option value="anual">Agregado Anual</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio">
                    </div>
                    <div class="col-4">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin">
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button id="generate-report-btn" class="btn btn-primary w-100">Generar Reporte</button>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <button id="export-pdf-btn" class="btn btn-danger" style="display:none;">
                    <i class="fas fa-file-pdf me-2"></i> Exportar a PDF
                </button>
            </div>

            <hr>

            <h4 class="mb-3" id="report-title">Reporte de Proyectos por Rango de Fecha</h4>
            <div id="report-results-container" class="table-responsive">
                <p class="text-center text-muted">Selecciona un tipo de reporte y haz clic en "Generar Reporte".</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        (function() {
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) {
                    return parts.pop().split(';').shift();
                }
                return null;
            }

            const API_BASE_URL = 'http://18.216.126.104/api';
            const token = getCookie('token');
            const userRole = localStorage.getItem('user_role');

            if (!token) {
                window.location.href = '/login';
                return;
            }

            if (userRole !== 'Lider') {
                document.getElementById('report-results-container').innerHTML = '<div class="alert alert-danger">Acceso Denegado: Solo los Líderes pueden acceder a los reportes.</div>';
                $('#generate-report-btn').prop('disabled', true);
            }

            $('#report-type').on('change', function() {
                const type = $(this).val();

                if (type === 'proyectos') {
                    $('#date-range-controls').show();
                } else {
                    $('#date-range-controls').hide();
                    $('#fecha_inicio').val('');
                    $('#fecha_fin').val('');
                    $('#tipo').val('');
                }

                $('#export-pdf-btn').hide();
                $('#report-title').text(`Reporte de ${type.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}`);
                $('#report-results-container').html('<p class="text-center text-muted">Haz clic en "Generar Reporte" para cargar los datos.</p>');
            }).trigger('change');

            $('#generate-report-btn').on('click', function() {
                if (userRole !== 'Lider' || !token) return;

                const type = $('#report-type').val();
                const tipoReporte = $('#tipo').val();
                let url = `${API_BASE_URL}/reportes/${type}`;
                const $results = $('#report-results-container');
                $results.html('<p class="text-center"><div class="spinner-border text-primary" role="status"></div> Cargando reporte...</p>');

                const params = {};
                if (type === 'proyectos') {
                    const fechaInicio = $('#fecha_inicio').val();
                    const fechaFin = $('#fecha_fin').val();

                    if (fechaInicio) params.fecha_inicio = fechaInicio;
                    if (fechaFin) params.fecha_fin = fechaFin;
                    if (tipoReporte) params.tipo = tipoReporte;

                    const queryString = Object.keys(params).map(key => key + '=' + encodeURIComponent(params[key])).join('&');
                    if(queryString) url += '?' + queryString;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'Authorization': 'Bearer ' + token },
                    success: function(response) {
                        const renderType = type === 'proyectos' && tipoReporte ? tipoReporte : type;
                        renderReportTable(renderType, response.data);
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Error de conexión o datos inválidos.';
                        $results.html(`<div class="alert alert-danger">Error al generar reporte: ${message}</div>`);
                        $('#export-pdf-btn').hide();
                    }
                });
            });

            function renderReportTable(type, data) {
                let headers = [];
                let rows = '';
                let tableHtml = '';

                if (!data || data.length === 0) {
                    $('#report-results-container').html('<div class="alert alert-info">No se encontraron resultados para los criterios seleccionados.</div>');
                    $('#export-pdf-btn').hide();
                    return;
                }

                const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString() : 'N/A';

                switch (type) {
                    case 'mensual':
                        headers = ['Año', 'Mes', 'Total de Proyectos'];
                        rows = data.map(r => `
                            <tr>
                                <td>${r.anio}</td>
                                <td>${r.mes}</td>
                                <td>${r.total}</td>
                            </tr>
                        `).join('');
                        break;
                    case 'anual':
                        headers = ['Año', 'Total de Proyectos'];
                        rows = data.map(r => `
                            <tr>
                                <td>${r.anio}</td>
                                <td>${r.total}</td>
                            </tr>
                        `).join('');
                        break;

                    case 'proyectos':
                        headers = ['ID', 'Nombre', 'Descripción', 'Tipo', 'Categoría', 'Estado', 'Avance (%)', 'F. Inicio', 'F. Fin Estimada'];
                        rows = data.map(p => `
                            <tr>
                                <td>${p.id}</td>
                                <td>${p.nombre}</td>
                                <td>${p.descripcion}</td>
                                <td>${p.tipo}</td>
                                <td>${p.categoria}</td>
                                <td><span class="badge bg-${p.estado === 'Finalizado' ? 'success' : p.estado === 'En Ejecución' ? 'info text-dark' : 'warning text-dark'}">${p.estado}</span></td>
                                <td>${parseFloat(p.porcentaje_avance).toFixed(2)}</td>
                                <td>${formatDate(p.fecha_inicio)}</td>
                                <td>${formatDate(p.fecha_fin_estimada)}</td>
                            </tr>
                        `).join('');
                        break;

                    case 'en-ejecucion':
                        headers = ['ID', 'Nombre', 'Avance (%)', 'Estado', 'Última Actualización'];
                        rows = data.map(p => `
                            <tr>
                                <td>${p.id}</td>
                                <td>${p.nombre}</td>
                                <td>${parseFloat(p.porcentaje_avance).toFixed(2)}</td>
                                <td><span class="badge bg-info text-dark">${p.estado}</span></td>
                                <td>${formatDate(p.updated_at)}</td>
                            </tr>
                        `).join('');
                        break;
                    case 'finalizados':
                        headers = ['ID', 'Nombre', 'Avance (%)', 'Resultado Final', 'Última Actualización'];
                        rows = data.map(p => `
                            <tr>
                                <td>${p.id}</td>
                                <td>${p.nombre}</td>
                                <td>${parseFloat(p.porcentaje_avance).toFixed(2)}</td>
                                <td>${p.resultado_final || 'N/A'}</td>
                                <td>${formatDate(p.updated_at)}</td>
                            </tr>
                        `).join('');
                        break;

                    case 'lideres':
                        headers = ['ID Líder', 'Nombre Líder', 'Proyectos Asignados', 'Progreso Promedio (%)'];
                        rows = data.map(l => `
                            <tr>
                                <td>${l.lider_id}</td>
                                <td>${l.nombre_lider}</td>
                                <td>${l.total}</td>
                                <td>${parseFloat(l.progreso_promedio).toFixed(2)}</td>
                            </tr>
                        `).join('');
                        break;
                    case 'clientes':
                        headers = ['ID Cliente', 'Nombre Cliente', 'Proyectos Solicitados', 'Progreso Promedio (%)'];
                        rows = data.map(c => `
                            <tr>
                                <td>${c.cliente_id}</td>
                                <td>${c.nombre_cliente}</td>
                                <td>${c.total}</td>
                                <td>${parseFloat(c.progreso_promedio).toFixed(2)}</td>
                            </tr>
                        `).join('');
                        break;
                    default:
                        $('#report-results-container').html('<div class="alert alert-danger">Tipo de reporte no reconocido o datos faltantes.</div>');
                        $('#export-pdf-btn').hide();
                        return;
                }

                tableHtml = `
                    <table class="table table-striped table-hover" id="report-table">
                        <thead>
                            <tr>
                                ${headers.map(h => `<th>${h}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                        </tbody>
                    </table>
                `;

                $('#report-results-container').html(tableHtml);
                $('#export-pdf-btn').show();
            }

            $('#export-pdf-btn').on('click', function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('p', 'mm', 'a4');

                const reportTitle = $('#report-title').text();
                const tableContainer = document.getElementById('report-results-container');

                doc.setFontSize(18);
                doc.text(reportTitle, 14, 20);
                doc.setFontSize(10);
                doc.text(`Fecha de Reporte: ${new Date().toLocaleDateString()}`, 14, 25);

                html2canvas(tableContainer, {
                    scale: 2,
                    logging: false,
                    useCORS: true
                }).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const imgWidth = 190;
                    const pageHeight = 295;
                    const imgHeight = canvas.height * imgWidth / canvas.width;
                    let heightLeft = imgHeight;

                    let position = 35;

                    doc.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;

                    while (heightLeft >= 0) {
                        position = heightLeft - imgHeight;
                        doc.addPage();
                        doc.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;
                    }

                    doc.save(reportTitle.replace(/ /g, '_') + '.pdf');
                });
            });
        })();
    </script>
@endsection
