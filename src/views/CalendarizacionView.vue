<template>
  <div class="calendarizacion-view">
    <!-- Botón de Calendarización -->
    <div class="calendarizacion-button-area">
      <button
        class="btn-calendarization"
        :class="{ active: showCalendar }"
        @click="toggleCalendar"
        :aria-pressed="showCalendar"
        aria-label="Ver calendarización de eventos"
      >
        <i class="fas fa-calendar-check" aria-hidden="true"></i>
        <span class="btn-label">Calendarización</span>
      </button>
    </div>

    <div class="calendar-header">
      <h1>Eventos Gastronómicos en El Salvador</h1>
      <p>Descubre festivales, ferias y eventos culinarios en todo el país</p>
    </div>
    
    <div class="calendar-controls">
      <div class="month-navigation">
        <button @click="changeMonth(-1)" aria-label="Mes anterior">
          <i class="fas fa-chevron-left"></i>
        </button>
        <h2>{{ currentMonth }} {{ currentYear }}</h2>
        <button @click="changeMonth(1)" aria-label="Mes siguiente">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      
      <div class="view-options">
        <button :class="{ active: viewMode === 'calendar' }" @click="viewMode = 'calendar'">
          <i class="fas fa-calendar-alt"></i> Calendario
        </button>
        <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">
          <i class="fas fa-list"></i> Lista
        </button>
      </div>
      
      <div class="filter-options">
        <select v-model="departmentFilter" aria-label="Filtrar por departamento">
          <option value="">Todos los departamentos</option>
          <option v-for="dept in departments" :key="dept" :value="dept">
            {{ dept }}
          </option>
        </select>
        
        <select v-model="categoryFilter" aria-label="Filtrar por categoría">
          <option value="">Todas las categorías</option>
          <option v-for="cat in categories" :key="cat" :value="cat">
            {{ cat }}
          </option>
        </select>
      </div>
    </div>
    
    <!-- Vista de Calendario -->
    <div class="calendar-container" v-if="viewMode === 'calendar' && showCalendar">
      <div class="calendar-grid">
        <div class="calendar-weekdays">
          <div v-for="day in weekDays" :key="day" class="weekday">
            {{ day }}
          </div>
        </div>
        
        <div class="calendar-days">
          <div 
            v-for="(day, index) in calendarDays" 
            :key="index" 
            class="day"
            :class="{
              'current-month': day.isCurrentMonth,
              'has-events': day.events.length > 0,
              'today': day.isToday
            }"
          >
            <div class="day-number">{{ day.date.getDate() }}</div>
            
            <div class="day-events">
              <div 
                v-for="event in day.events.slice(0, 3)" 
                :key="event.id" 
                class="event-indicator"
                :style="{ backgroundColor: getCategoryColor(event.category) }"
                :title="event.title"
                @click="showEventDetails(event)"
              >
                {{ event.title.substring(0, 15) }}...
              </div>
              <div v-if="day.events.length > 3" class="more-events">
                +{{ day.events.length - 3 }} más
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Vista de Lista -->
    <div class="events-list" v-if="viewMode === 'list' && showCalendar">
      <div class="event-card" v-for="event in filteredEvents" :key="event.id">
        <div class="event-image">
          <img :src="event.image" :alt="event.title" />
          <div class="event-category" :style="{ backgroundColor: getCategoryColor(event.category) }">
            {{ event.category }}
          </div>
        </div>
        
        <div class="event-content">
          <h3 class="event-title">{{ event.title }}</h3>
          
          <div class="event-info">
            <div class="info-item">
              <i class="fas fa-calendar-alt"></i>
              <span>{{ formatDate(event.date) }}</span>
            </div>
            
            <div class="info-item">
              <i class="fas fa-clock"></i>
              <span>{{ event.time }}</span>
            </div>
            
            <div class="info-item">
              <i class="fas fa-map-marker-alt"></i>
              <span>{{ event.location }}</span>
            </div>
          </div>
          
          <p class="event-description">{{ event.shortDescription }}</p>
          
          <div class="event-actions">
            <button class="btn-details" @click="showEventDetails(event)" :aria-label="`Ver detalles ${event.title}`">
              <i class="fas fa-info-circle"></i>
              Ver detalles
            </button>
            
            <button class="btn-save" @click="saveEvent(event)" :aria-label="`Guardar ${event.title}`">
              <i class="fas fa-bookmark"></i>
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Eventos destacados -->
    <div class="featured-events" v-if="viewMode === 'calendar' && showCalendar">
      <h3>Eventos destacados de {{ currentMonth }}</h3>
      <div class="featured-container">
        <div class="event-card" v-for="event in featuredEvents" :key="event.id">
          <div class="event-image">
            <img :src="event.image" :alt="event.title" />
            <div class="event-category" :style="{ backgroundColor: getCategoryColor(event.category) }">
              {{ event.category }}
            </div>
          </div>
          
          <div class="event-content">
            <h3 class="event-title">{{ event.title }}</h3>
            
            <div class="event-info">
              <div class="info-item">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ formatDate(event.date) }}</span>
              </div>
              
              <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>{{ event.time }}</span>
              </div>
              
              <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ event.location }}</span>
              </div>
            </div>
            
            <p class="event-description">{{ event.shortDescription }}</p>
            
            <div class="event-actions">
              <button class="btn-details" @click="showEventDetails(event)">
                <i class="fas fa-info-circle"></i>
                Ver detalles
              </button>
              
              <button class="btn-save" @click="saveEvent(event)">
                <i class="fas fa-bookmark"></i>
                Guardar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de detalles del evento -->
    <div class="modal-overlay" v-if="selectedEvent" @click.self="closeModal">
      <div class="modal-content">
        <button class="close-modal" @click="closeModal">&times;</button>
        
        <div class="modal-header">
          <h3>{{ selectedEvent.title }}</h3>
          <div class="event-meta">
            <span><i class="fas fa-calendar-alt"></i> {{ formatDate(selectedEvent.date) }}</span>
            <span><i class="fas fa-clock"></i> {{ selectedEvent.time }}</span>
            <span><i class="fas fa-tag"></i> {{ selectedEvent.category }}</span>
          </div>
        </div>
        
        <div class="event-location">
          <i class="fas fa-map-marker-alt"></i> {{ selectedEvent.location }}, {{ selectedEvent.department }}
        </div>
        
        <div class="event-organizer">
          <i class="fas fa-building"></i> Organizado por: {{ selectedEvent.organizer }}
        </div>
        
        <p class="event-description">{{ selectedEvent.description }}</p>
        
        <div class="event-reviews" v-if="selectedEvent.reviews && selectedEvent.reviews.length">
          <h4>Reseñas de participantes</h4>
          <div class="review" v-for="(review, index) in selectedEvent.reviews" :key="index">
            "{{ review }}"
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CalendarizacionView',
  data() {
    return {
      showCalendar: true,
      viewMode: 'calendar',
      currentDate: new Date(),
      departmentFilter: '',
      categoryFilter: '',
      selectedEvent: null,
      events: [
        {
          id: "pupusas",
          title: "Festival de Pupusas 2025",
          date: "2025-10-15",
          time: "10:00 AM - 8:00 PM",
          location: "Parque Central de San Salvador",
          description: "El evento más grande de pupusas tradicionales con más de 50 puestos y competencias de comida en vivo. Disfruta de variedades únicas como pupusas de loroco con queso, revueltas con ayote. Habrá talleres gratuitos para aprender a hacer pupusas, zona infantil con actividades y presentaciones culturales de música y danza tradicional.",
          shortDescription: "El festival más grande de pupusas en El Salvador con más de 50 puestos y actividades culturales.",
          image: "pupusas.jpeg",
          category: "Festival",
          department: "San Salvador",
          organizer: "Municipalidad de San Salvador",
          frequency: "Anual",
          featured: true,
          reviews: [
            "¡Las mejores pupusas que he probado en mi vida! La masa estaba perfecta - Carlos M.",
            "Llevé a mis hijos y aprendimos a hacer pupusas juntos. Experiencia inolvidable - Ana R.",
            "La variedad de curtidos era impresionante. ¡Volveré el próximo año! - Luis H."
          ]
        },
        {
          id: "maiz",
          title: "Festival del Maíz en Alegría",
          date: "2025-08-18",
          time: "9:00 AM - 5:00 PM",
          location: "Alegría, Usulután",
          description: "Festival organizado por la parroquia San Pedro Apóstol para recaudar fondos. Disfruta de platillos tradicionales basados en maíz, como atoles, tortillas, tamales y más. Evento familiar con música en vivo, venta de artesanías y actividades para niños.",
          shortDescription: "Festival tradicional del maíz en el pintoresco pueblo de Alegría, Usulután.",
          image: "maiz.png",
          category: "Festival",
          department: "Usulután",
          organizer: "Parroquia San Pedro Apóstol",
          frequency: "Anual",
          featured: true,
          reviews: [
            "El ambiente en Alegría es mágico. Las vistas y la comida son increíbles - Sofía L.",
            "Los tamales de elote eran los mejores que he probado - Javier P.",
            "Evento familia perfecto, mis hijos disfrutaron mucho - Marta G."
          ]
        },
        {
          id: "cafe",
          title: "Ruta del Café Artesanal",
          date: "2025-11-05",
          time: "8:00 AM - 4:00 PM",
          location: "Fincas Cafetaleras de Apaneca",
          description: "Recorrido guiado por las fincas cafetaleras más emblemáticas de Apaneca. Conoce el proceso completo desde la cosecha hasta la taza, con catación de variedades premium. Incluye transporte desde San Salvador, desayuno típico, almuerzo y muestras de café para llevar. Visita a cultivos orgánicos y proceso de tostado artesanal.",
          shortDescription: "Recorrido por fincas cafetaleras de Apaneca con catación de variedades premium.",
          image: "https://images.unsplash.com/photo-1442512595331-e89e73853f31?w=400&h=300&fit=crop",
          category: "Tour",
          department: "Ahuachapán",
          organizer: "Asociación de Caficultores",
          frequency: "Trimestral",
          featured: true,
          reviews: [
            "El tour por los cafetales al amanecer fue mágico - Roberto S.",
            "Nunca había apreciado tanto el trabajo detrás de una taza de café - Laura T.",
            "Los cafés gourmet eran exquisitos, especialmente el Pacamara - Diego M."
          ]
        },
        {
          id: "mercado",
          title: "Mercado Gastronómico Nocturno",
          date: "2025-11-18",
          time: "5:00 PM - 11:00 PM",
          location: "Plaza El Salvador",
          description: "Evento nocturno donde podrás disfrutar de lo mejor de la comida callejera salvadoreña en un ambiente festivo. Más de 30 puestos ofreciendo platillos tradicionales y fusiones innovadoras. Habrá barra de cócteles artesanales, música en vivo y área de juegos tradicionales. Perfecto para familias y grupos de amigos.",
          shortDescription: "Mercado nocturno con lo mejor de la comida callejera salvadoreña y música en vivo.",
          image: "https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=400&h=300&fit=crop",
          category: "Mercado",
          department: "San Salvador",
          organizer: "Plaza El Salvador",
          frequency: "Mensual",
          featured: true,
          reviews: [
            "Las enchiladas estaban para chuparse los dedos - Patricia V.",
            "El ambiente con las luces y la música era increíble - Oscar R.",
            "Probé platillos que nunca había visto antes - Gabriela C."
          ]
        },
        {
          id: "postres",
          title: "Concurso de Postres Tradicionales",
          date: "2025-11-25",
          time: "1:00 PM - 6:00 PM",
          location: "Salón de Eventos La Flor",
          description: "Evento donde los mejores reposteros del país competirán por el título del mejor postre tradicional. Los asistentes podrán votar por su favorito y disfrutar de muestras gratuitas. Habrá categorías para semitas, quesadillas, marquesotes, atoles y postres innovadores basados en recetas tradicionales. Zona de venta para llevar postres a casa.",
          shortDescription: "Concurso nacional de postres tradicionales salvadoreños con degustaciones.",
          image: "https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?w=400&h=300&fit=crop",
          category: "Concurso",
          department: "San Salvador",
          organizer: "Asociación de Reposteros",
          frequency: "Anual",
          featured: true,
          reviews: [
            "La semita de piña ganó mi corazón - Luis H.",
            "Nunca había probado tantas variedades de marquesote - Daniela P.",
            "Los jueces eran muy conocedores y explicaban bien - Jorge R."
          ]
        },
        {
          id: "mariscos",
          title: "Festival del Marisco",
          date: "2025-12-02",
          time: "11:00 AM - 9:00 PM",
          location: "Playa El Tunco",
          description: "Disfruta de los más frescos mariscos preparados por chefs locales frente al mar. Evento con puestos de ceviches, cócteles, mariscadas y platillos innovadores. Habrá zona de hamacas, música en vivo y espectáculo de danza folklórica al atardecer. Perfecto para un día de playa con buena comida.",
          shortDescription: "Festival de mariscos frescos en la playa El Tunco con música y actividades.",
          image: "https://images.unsplash.com/photo-1559339352-11d035aa65de?w=400&h=300&fit=crop",
          category: "Festival",
          department: "La Libertad",
          organizer: "Comité de Turismo de La Libertad",
          frequency: "Anual",
          featured: true,
          reviews: [
            "Los cócteles de concha eran increíblemente frescos - María G.",
            "Ver el atardecer mientras comíamos fue inolvidable - Juan P.",
            "La atención en los puestos era muy buena - Carlos M."
          ]
        }
      ],
      weekDays: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
      months: [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
      ],
      departments: [
        'Ahuachapán', 'Cabañas', 'Chalatenango', 'Cuscatlán', 'La Libertad',
        'La Paz', 'La Unión', 'Morazán', 'San Miguel', 'San Salvador',
        'San Vicente', 'Santa Ana', 'Sonsonate', 'Usulután'
      ],
      categories: [
        'Festival', 'Mercado', 'Concurso', 'Taller', 'Tour', 'Feria'
      ]
    }
  },
  computed: {
    currentYear() {
      return this.currentDate.getFullYear();
    },
    currentMonth() {
      return this.months[this.currentDate.getMonth()];
    },
    filteredEvents() {
      return this.events.filter(event => {
        const matchesDepartment = this.departmentFilter ? 
          event.department === this.departmentFilter : true;
        
        const matchesCategory = this.categoryFilter ? 
          event.category === this.categoryFilter : true;
        
        return matchesDepartment && matchesCategory;
      });
    },
    featuredEvents() {
      return this.filteredEvents.filter(event => event.featured);
    },
    calendarDays() {
      const year = this.currentDate.getFullYear();
      const month = this.currentDate.getMonth();
      
      // Primer día del mes
      const firstDay = new Date(year, month, 1);
      
      // Último día del mes
      const lastDay = new Date(year, month + 1, 0);
      
      // Días en el mes
      const daysInMonth = lastDay.getDate();
      
      // Día de la semana en que comienza el mes (0 = Domingo, 1 = Lunes, etc.)
      const startDay = firstDay.getDay();
      
      // Calcular días del mes anterior a mostrar
      const prevMonthDays = startDay;
      const prevMonthLastDay = new Date(year, month, 0).getDate();
      
      // Calcular días del próximo mes a mostrar
      const totalCells = 42; // 6 semanas * 7 días
      const nextMonthDays = totalCells - (daysInMonth + startDay);
      
      const days = [];
      
      // Añadir días del mes anterior
      for (let i = prevMonthLastDay - prevMonthDays + 1; i <= prevMonthLastDay; i++) {
        const date = new Date(year, month - 1, i);
        days.push({
          date: date,
          isCurrentMonth: false,
          isToday: this.isToday(date),
          events: this.getEventsForDate(date)
        });
      }
      
      // Añadir días del mes actual
      for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(year, month, i);
        days.push({
          date: date,
          isCurrentMonth: true,
          isToday: this.isToday(date),
          events: this.getEventsForDate(date)
        });
      }
      
      // Añadir días del próximo mes
      for (let i = 1; i <= nextMonthDays; i++) {
        const date = new Date(year, month + 1, i);
        days.push({
          date: date,
          isCurrentMonth: false,
          isToday: this.isToday(date),
          events: this.getEventsForDate(date)
        });
      }
      
      return days;
    }
  },
  methods: {
    toggleCalendar() {
      this.showCalendar = !this.showCalendar;
    },
    changeMonth(step) {
      const newDate = new Date(this.currentDate);
      newDate.setMonth(newDate.getMonth() + step);
      this.currentDate = newDate;
    },
    isToday(date) {
      const today = new Date();
      return date.getDate() === today.getDate() &&
             date.getMonth() === today.getMonth() &&
             date.getFullYear() === today.getFullYear();
    },
    formatDate(date) {
      const [year, month, day] = date.split('-');
      return `${parseInt(day)} de ${this.months[parseInt(month) - 1]} de ${year}`;
    },
    getEventsForDate(date) {
      const dateStr = date.toISOString().split('T')[0];
      return this.events.filter(event => event.date === dateStr);
    },
    getCategoryColor(category) {
      const colors = {
        'Festival': '#ff6f61',
        'Mercado': '#4caf50',
        'Concurso': '#2196f3',
        'Taller': '#ff9800',
        'Tour': '#9c27b0',
        'Feria': '#e91e63'
      };
      
      return colors[category] || '#607d8b';
    },
    showEventDetails(event) {
      this.selectedEvent = event;
    },
    closeModal() {
      this.selectedEvent = null;
    },
    saveEvent(event) {
      console.log('Evento guardado:', event.id);
      alert(`Evento guardado: ${event.title}`);
    }
  }
}
</script>

<style scoped>
.calendarizacion-view {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Área del botón */
.calendarizacion-button-area {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 18px;
}

/* Botón Calendarización */
.btn-calendarization {
  background: #f0f0f0;
  color: #333;
  border: none;
  padding: 12px 20px;
  min-width: 180px;
  height: 48px;
  border-radius: 28px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  font-size: 1rem;
}

.btn-calendarization i {
  font-size: 1.05rem;
}

.btn-calendarization.active {
  background: #ff6f61;
  color: #ffffff;
  box-shadow: 0 8px 20px rgba(255,111,97,0.28);
  transform: translateY(-1px);
}

.btn-calendarization:hover:not(.active) {
  background: #e9e9e9;
  transform: translateY(-1px);
}

.btn-calendarization:focus {
  outline: 3px solid rgba(255,111,97,0.18);
  outline-offset: 3px;
  box-shadow: 0 8px 20px rgba(255,111,97,0.18);
}

.btn-label {
  display: inline-block;
}

/* Encabezado */
.calendar-header {
  text-align: center;
  margin-bottom: 30px;
}

.calendar-header h1 {
  font-size: 2.5rem;
  color: #222;
  margin-bottom: 10px;
}

.calendar-header p {
  font-size: 1.2rem;
  color: #555;
  max-width: 700px;
  margin: 0 auto;
}

/* Controles */
.calendar-controls {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
  padding: 15px;
  background: #f8f8f8;
  border-radius: 12px;
}

.month-navigation {
  display: flex;
  align-items: center;
  gap: 15px;
}

.month-navigation button {
  background: #ff6f61;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 1.1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s;
}

.month-navigation button:hover {
  background: #e0574e;
}

.month-navigation h2 {
  margin: 0;
  min-width: 200px;
  text-align: center;
  color: #333;
}

.view-options {
  display: flex;
  gap: 10px;
}

.view-options button {
  background: #f0f0f0;
  border: none;
  padding: 8px 15px;
  border-radius: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
}

.view-options button.active {
  background: #ff6f61;
  color: white;
}

.view-options button:hover:not(.active) {
  background: #e0e0e0;
}

.filter-options {
  display: flex;
  gap: 10px;
}

.filter-options select {
  padding: 8px 15px;
  border-radius: 20px;
  border: 1px solid #ddd;
  background: white;
  min-width: 180px;
}

/* Calendario */
.calendar-grid {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background: #ff6f61;
  color: white;
  font-weight: bold;
}

.weekday {
  padding: 15px 0;
  text-align: center;
}

.calendar-days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #e0e0e0;
}

.day {
  background: white;
  min-height: 120px;
  padding: 10px;
  position: relative;
}

.day.current-month {
  background: white;
}

.day:not(.current-month) {
  background: #f9f9f9;
  color: #aaa;
}

.day.today {
  background: #fff9e6;
}

.day.today .day-number {
  font-weight: bold;
  color: #ff6f61;
}

.day.has-events {
  border-bottom: 2px solid #ff6f61;
}

.day-number {
  font-size: 1.2rem;
  margin-bottom: 5px;
}

.day-events {
  max-height: 90px;
  overflow-y: auto;
}

.event-indicator {
  font-size: 0.8rem;
  padding: 3px 6px;
  border-radius: 4px;
  color: white;
  margin-bottom: 3px;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
}

.event-indicator:hover {
  opacity: 0.9;
}

.more-events {
  font-size: 0.7rem;
  color: #666;
  font-style: italic;
  margin-top: 3px;
}

/* Lista de eventos */
.events-list {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.featured-events {
  margin-top: 40px;
}

.featured-events h3 {
  text-align: center;
  margin-bottom: 25px;
  color: #222;
  font-size: 1.8rem;
  position: relative;
}

.featured-events h3::after {
  content: "";
  display: block;
  width: 100px;
  height: 4px;
  background: #ff6f61;
  margin: 10px auto 0;
  border-radius: 2px;
}

.featured-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 25px;
}

/* Tarjetas de eventos */
.event-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.event-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.event-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.event-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.event-card:hover .event-image img {
  transform: scale(1.05);
}

.event-category {
  position: absolute;
  top: 15px;
  right: 15px;
  background: #ff6f61;
  color: white;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.event-content {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.event-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #222;
  margin: 0 0 15px 0;
  line-height: 1.3;
}

.event-info {
  margin-bottom: 15px;
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  color: #555;
}

.info-item i {
  width: 20px;
  color: #ff6f61;
  margin-right: 10px;
  font-size: 0.9rem;
}

.info-item span {
  font-size: 0.95rem;
  font-weight: 500;
}

.event-description {
  color: #666;
  line-height: 1.5;
  margin-bottom: 20px;
  flex: 1;
}

.event-actions {
  display: flex;
  gap: 10px;
  margin-top: auto;
}

.btn-details, .btn-save {
  flex: 1;
  padding: 10px 15px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-details {
  background: #ff6f61;
  color: white;
}

.btn-details:hover {
  background: #e0574e;
}

.btn-save {
  background: #f0f0f0;
  color: #555;
  border: 1px solid #ddd;
}

.btn-save:hover {
  background: #e0e0e0;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 25px;
  position: relative;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.close-modal {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  font-size: 1.8rem;
  cursor: pointer;
  color: #777;
}

.close-modal:hover {
  color: #333;
}

.modal-header {
  margin-bottom: 20px;
}

.modal-header h3 {
  color: #005C34;
  font-size: 1.6rem;
  margin-bottom: 5px;
}

.event-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 15px;
  color: #666;
}

.event-meta span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.event-location, .event-organizer {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  color: #555;
}

.event-description {
  line-height: 1.6;
  margin-bottom: 20px;
}

.event-reviews {
  margin-top: 25px;
}

.event-reviews h4 {
  margin-bottom: 10px;
  color: #005C34;
}

.review {
  background: #f9f9f9;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 10px;
  font-style: italic;
}

/* Responsive */
@media (max-width: 992px) {
  .calendar-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .month-navigation {
    justify-content: center;
  }
  
  .view-options, .filter-options {
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .calendar-header h1 {
    font-size: 2rem;
  }
  
  .calendar-header p {
    font-size: 1rem;
  }
  
  .day {
    min-height: 100px;
  }
  
  .featured-container {
    grid-template-columns: 1fr;
  }
  
  .filter-options {
    flex-direction: column;
  }
  
  .event-actions {
    flex-direction: column;
  }
  
  .event-title {
    font-size: 1.2rem;
  }
  
  .event-image {
    height: 180px;
  }
}

@media (max-width: 576px) {
  .calendar-weekdays, .calendar-days {
    grid-template-columns: repeat(7, 1fr);
    font-size: 0.9rem;
  }
  
  .day {
    padding: 5px;
    min-height: 80px;
  }
  
  .day-number {
    font-size: 1rem;
  }
  
  .event-indicator {
    font-size: 0.7rem;
    padding: 2px 4px;
  }
  
  .event-content {
    padding: 15px;
  }
  
  .event-title {
    font-size: 1.1rem;
  }
  
  .info-item {
    font-size: 0.9rem;
  }
  
  .event-description {
    font-size: 0.9rem;
  }

  .btn-label {
    display: none;
  }
}
</style>