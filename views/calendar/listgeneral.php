<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'CALENDARIO';
?>

<?php
$script = <<<JS
  document.addEventListener('DOMContentLoaded', function() {
    var filtroTipo = '';

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      height: '100%',
      locale: 'es',
      expandRows: true,
      slotMinTime: '08:00',
      slotMaxTime: '20:00',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      initialView: 'dayGridMonth',
      navLinks: true,
      editable: false,
      selectable: true,
      nowIndicator: true,
      dayMaxEvents: false,
      events: '/calendar/eventos',
      
      eventDidMount: function(info) {
          const tipo = info.event.extendedProps.tipo || '';
          const origen = info.event.extendedProps.origen || '';

          // Filtro dinámico
          if (filtroTipo && tipo !== filtroTipo) {
              info.el.style.display = 'none';
          }

          // Tooltip origen
          info.el.setAttribute("title", origen ? 'Pertenece a ' + origen : '');
      },
      
      eventClick: function(info) {
          if (info.event.url) {
              window.open(info.event.url, "_blank");
              info.jsEvent.preventDefault();
          }
      },
    });

    calendar.render();

    // Escuchar cambios del filtro
    document.getElementById('filtro-tipo').addEventListener('change', function(e) {
        filtroTipo = e.target.value;
        calendar.refetchEvents(); // recarga eventos y aplica filtro
    });
  });
JS;
$this->registerJs($script, \yii\web\View::POS_HEAD);
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row">
            <div class="col-12">
                <h1><?= Html::encode($this->title) ?> </h1>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-3 flex-wrap">
                    <!-- Leyenda -->
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span><strong>🟦</strong> Proyecto</span>
                        <span><strong>🟩</strong> Campaña</span>
                        <span><strong>🟨</strong> Asignación</span>
                        <span><strong>🟥</strong> Recurso</span>
                        <span><strong>✅</strong> Finalizado</span>
                    </div>

                    <!-- Filtro -->
                    <div class="mt-2 mt-md-0">
                        <label for="filtro-tipo" class="form-label mb-1">Filtrar por:</label>
                        <select id="filtro-tipo" class="form-select selecgurlso" style="min-width: 200px;">
                            <option value="">Todos</option>
                            <option value="proyecto">Proyectos</option>
                            <option value="campania">Campañas</option>
                            <option value="tarea">Asignaciones</option>
                            <option value="recurso">Recursos</option>
                        </select>
                    </div>
                </div>


            </div>

            <div id='calendar-container'>
                <div id='calendar'></div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>
