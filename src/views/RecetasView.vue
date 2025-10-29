<template>
  <div class="recetas-view">
    <!-- Encabezado de la sección -->
    <div class="recetas-header">
      <h1>Recetas Tradicionales de El Salvador</h1>
      <p>Descubre los sabores auténticos de la cocina salvadoreña con estas recetas paso a paso</p>
    </div>

    <!-- Filtros y búsqueda MEJORADA -->
    <div class="recetas-filtros">
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Buscar recetas..." 
          aria-label="Buscar recetas"
          @keyup.enter="performSearch"
        >
      </div>
      
      <div class="filter-options">
        <select v-model="categoriaFiltro" aria-label="Filtrar por categoría">
          <option value="">Todas las categorías</option>
          <option v-for="categoria in categorias" :key="categoria" :value="categoria">
            {{ categoria }}
          </option>
        </select>
        
        <select v-model="dificultadFiltro" aria-label="Filtrar por dificultad">
          <option value="">Todas las dificultades</option>
          <option v-for="dificultad in dificultades" :key="dificultad" :value="dificultad">
            {{ dificultad }}
          </option>
        </select>
      </div>

      <div v-if="searchPerformed" class="results-info">
        <p>Mostrando resultados para: <strong>"{{ searchQuery }}"</strong></p>
        <p v-if="categoriaFiltro">Categoría: <strong>{{ categoriaFiltro }}</strong></p>
        <p v-if="dificultadFiltro">Dificultad: <strong>{{ dificultadFiltro }}</strong></p>
      </div>
    </div>

    <!-- Resto del código se mantiene igual -->
    <!-- Vista de Galería/Lista -->
    <div class="view-toggle">
      <button 
        :class="{ active: vista === 'galeria' }" 
        @click="vista = 'galeria'"
        aria-label="Ver en formato galería"
      >
        <i class="fas fa-th"></i> Galería
      </button>
      <button 
        :class="{ active: vista === 'lista' }" 
        @click="vista = 'lista'"
        aria-label="Ver en formato lista"
      >
        <i class="fas fa-list"></i> Lista
      </button>
    </div>

    <!-- Contenedor de recetas -->
    <div class="recetas-container" :class="vista">
      <div 
        v-for="receta in recetasFiltradas" 
        :key="receta.id" 
        class="receta-card"
        :class="vista"
      >
        <div class="receta-image">
          <img :src="receta.imagen" :alt="'Imagen de ' + receta.nombre" @error="replaceImage">
          <div class="receta-category">{{ receta.categoria }}</div>
          <div class="receta-difficulty" :class="receta.dificultad.toLowerCase()">
            {{ receta.dificultad }}
          </div>
        </div>
        
        <div class="receta-content">
          <h3 class="receta-title">{{ receta.nombre }}</h3>
          
          <div class="receta-meta">
            <span class="time">
              <i class="fas fa-clock"></i> {{ receta.tiempoPreparacion }}
            </span>
            <span class="servings">
              <i class="fas fa-users"></i> {{ receta.porciones }} porciones
            </span>
          </div>
          
          <p class="receta-description">{{ receta.descripcion }}</p>
          
          <div class="receta-ingredients">
            <h4>Ingredientes principales:</h4>
            <ul>
              <li v-for="(ingrediente, index) in receta.ingredientes.slice(0, 4)" :key="index">
                {{ ingrediente }}
              </li>
              <li v-if="receta.ingredientes.length > 4">y {{ receta.ingredientes.length - 4 }} más...</li>
            </ul>
          </div>
          
          <button class="btn-view-recipe" @click="verRecetaCompleta(receta)">
            <i class="fas fa-utensils"></i> Ver receta completa
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de receta completa -->
    <div class="modal-overlay" v-if="recetaSeleccionada" @click.self="cerrarModal">
      <div class="modal-content receta-modal">
        <button class="close-modal" @click="cerrarModal">&times;</button>
        
        <div class="receta-completa">
          <div class="receta-completa-header">
            <h2>{{ recetaSeleccionada.nombre }}</h2>
            <div class="receta-meta-completa">
              <span class="category">{{ recetaSeleccionada.categoria }}</span>
              <span class="time">
                <i class="fas fa-clock"></i> {{ recetaSeleccionada.tiempoPreparacion }}
              </span>
              <span class="servings">
                <i class="fas fa-users"></i> {{ recetaSeleccionada.porciones }} porciones
              </span>
              <span class="difficulty" :class="recetaSeleccionada.dificultad.toLowerCase()">
                {{ recetaSeleccionada.dificultad }}
              </span>
            </div>
          </div>
          
          <div class="receta-completa-content">
            <div class="receta-completa-image">
              <img :src="recetaSeleccionada.imagen" :alt="'Imagen de ' + recetaSeleccionada.nombre">
            </div>
            
            <div class="receta-completa-details">
              <div class="ingredientes-completos">
                <h3>Ingredientes</h3>
                <ul>
                  <li v-for="(ingrediente, index) in recetaSeleccionada.ingredientes" :key="index">
                    {{ ingrediente }}
                  </li>
                </ul>
              </div>
              
              <div class="instrucciones-completas">
                <h3>Instrucciones de Preparación</h3>
                <ol>
                  <li v-for="(instruccion, index) in recetaSeleccionada.instrucciones" :key="index">
                    {{ instruccion }}
                  </li>
                </ol>
              </div>
              
              <div class="receta-tips" v-if="recetaSeleccionada.consejos">
                <h3>Consejos y Tips</h3>
                <p>{{ recetaSeleccionada.consejos }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RecetasView',
  data() {
    return {
      searchQuery: '',
      categoriaFiltro: '',
      dificultadFiltro: '',
      vista: 'galeria',
      recetaSeleccionada: null,
      searchPerformed: false,
      recetas: [
        {
          id: 1,
          nombre: "Pupusas",
          imagen: "pupusas.jpeg",
          categoria: "Plato Principal",
          dificultad: "Media",
          tiempoPreparacion: "45 minutos",
          porciones: "4 porciones",
          descripcion: "Las pupusas son el plato más emblemático de El Salvador. Esta versión lleva una mezcla de frijoles, queso y chicharrón.",
          ingredientes: [
            "4 tazas de masa de maíz",
            "2 tazas de frijoles refritos",
            "1 taza de queso duro rallado",
            "1 taza de chicharrón molido",
            "1 cebolla picada",
            "Sal al gusto",
            "Aceite para freír"
          ],
          instrucciones: [
            "Mezclar la masa de maíz con agua y sal hasta obtener una consistencia suave y manejable.",
            "Dividir la masa en porciones del tamaño de una pelota de golf.",
            "Formar un hueco en cada porción y rellenar con una cucharada de la mezcla de frijoles, queso y chicharrón.",
            "Cerrar cuidadosamente la masa alrededor del relleno y aplanar suavemente para formar un disco de aproximadamente 10 cm de diámetro.",
            "Calentar un comal o sartén a fuego medio и cocinar cada pupusa durante 3-4 minutos por lado, hasta que estén doradas.",
            "Servir calientes con curtido y salsa de tomate."
          ],
          consejos: "Para mejores resultados, deje reposar la masa 10 minutos antes de formar las pupusas. Si la masa se pega a sus manos, humedézcalas ligeramente con agua."
        },
        {
          id: 2,
          nombre: "Sopa de Pata",
          imagen: "sopapata.png",
          categoria: "Sopa",
          dificultad: "Alta",
          tiempoPreparacion: "3 horas",
          porciones: "6 porciones",
          descripcion: "Una sopa sustanciosa y tradicional hecha con patas de res, verduras y especias.",
          ingredientes: [
            "4 patas de res limpias y cortadas",
            "2 plátanos verdes pelados y cortados",
            "2 elotes cortados en rodajas",
            "1 repollo pequeño picado",
            "2 zanahorias picadas",
            "1 cebolla picada",
            "2 dientes de ajo machacados",
            "1 chile verde picado",
            "Cilantro y hierbabuena al gusto",
            "Sal y pimienta al gusto"
          ],
          instrucciones: [
            "En una olla grande, cocinar las patas de res con suficiente agua durante 2 horas o hasta que estén tiernas.",
            "Agregar los plátanos verdes y el elote, cocinar por 30 minutos más.",
            "Incorporar las zanahorias, cebolla, ajo y chile verde. Cocinar por 20 minutos.",
            "Añadir el repollo, cilantro y hierbabuena. Cocinar por 10 minutos más.",
            "Sazonar con sal y pimienta al gusto.",
            "Servir caliente con limón y tortillas."
          ],
          consejos: "Para un sabor más intenso, puede dorar ligeramente las patas antes de hervirlas. Sirva con gotas de limón para realzar los sabores."
        },
        {
          id: 3,
          nombre: "Atol de Elote",
          imagen: "atolelote.png",
          categoria: "Bebida",
          dificultad: "Fácil",
          tiempoPreparacion: "30 minutos",
          porciones: "4 porciones",
          descripcion: "Bebida caliente y dulce tradicional, hecha a base de maíz tierno.",
          ingredientes: [
            "6 elotes tiernos",
            "4 tazas de agua",
            "1 raja de canela",
            "1 taza de azúcar",
            "1 cucharadita de sal",
            "1 cucharadita de esencia de vainilla"
          ],
          instrucciones: [
            "Desgranar los elotes y licuar los granos con 2 tazas de agua.",
            "Colar la mezcla para obtener la leche de elote.",
            "En una olla, hervir las 2 tazas restantes de agua con la canela.",
            "Agregar la leche de elote colada y cocinar a fuego medio, moviendo constantemente.",
            "Incorporar el azúcar, sal y vainilla. Continuar cocinando por 15 minutos hasta que espese.",
            "Servir caliente espolvoreado con canela en polvo."
          ],
          consejos: "Para una textura más suave, cuele la mezcla dos veces. Ajuste el azúcar según su preferencia dulce."
        },
        {
          id: 4,
          nombre: "Quesadilla Salvadoreña",
          imagen: "quesadilla.png",
          categoria: "Postre",
          dificultad: "Media",
          tiempoPreparacion: "1 hora",
          porciones: "8 porciones",
          descripcion: "Pan dulce tradicional, diferente a la quesadilla mexicana. Perfecto para acompañar el café.",
          ingredientes: [
            "2 tazas de harina de arroz",
            "1 taza de azúcar",
            "1 taza de queso duro rallado",
            "1 taza de crema agria",
            "4 huevos",
            "1/2 taza de mantequilla derretida",
            "1 cucharadita de polvo de hornear",
            "1 cucharadita de ajonjolí para decorar"
          ],
          instrucciones: [
            "Precalentar el horno a 350°F (180°C) y engrasar un molde rectangular.",
            "En un bowl, mezclar la harina de arroz, azúcar, queso rallado y polvo de hornear.",
            "En otro bowl, batir los huevos y agregar la crema agria y mantequilla derretida.",
            "Combinar las mezclas húmeda y seca hasta integrar completamente.",
            "Verter la masa en el molde preparado y espolvorear con ajonjolí.",
            "Hornear por 30-35 minutos o hasta que al insertar a palillo salga limpio.",
            "Dejar enfriar antes de cortar en cuadrados."
          ],
          consejos: "Para un sabor más auténtico, use queso duro salvadoreño. Sirva a temperatura ambiente con una taza de café."
        },
        {
          id: 5,
          nombre: "Yuca Frita con Chicharrón",
          imagen: "yuca.png",
          categoria: "Aperitivo",
          dificultad: "Fácil",
          tiempoPreparacion: "40 minutos",
          porciones: "4 porciones",
          descripcion: "Yuca cocida y frita acompañada de chicharrón crujiente. Un clásico de la comida callejera.",
          ingredientes: [
            "2 libras de yuca pelada y cortada",
            "1 libra de chicharrón",
            "2 dientes de ajo machacados",
            "Sal al gusto",
            "Aceite para freír",
            "Curtido para acompañar"
          ],
          instrucciones: [
            "En una olla con agua, cocinar la yuca con ajo y sal hasta que esté tierna pero firme (unos 20 minutos).",
            "Escurrir la yuca y dejar enfriar.",
            "Calentar abundante aceite en un sartén profundo.",
            "Freír la yuca hasta que esté dorada y crujiente por fuera.",
            "En el mismo aceite, freír el chicharrón hasta que quede crujiente.",
            "Servir la yuca frita con chicharrón y curtido al lado."
          ],
          consejos: "Para yucas más crujientes, séquelas bien después de cocinarlas antes de freír. El curtido casero hace la diferencia."
        },
        {
          id: 6,
          nombre: "Enchiladas Salvadoreñas",
          imagen: "enchilada.png",
          categoria: "Aperitivo",
          dificultad: "Media",
          tiempoPreparacion: "1 hora",
          porciones: "6 porciones",
          descripcion: "Tortillas fritas cubiertas con carne, verduras y salsa. Diferentes a las enchiladas mexicanas.",
          ingredientes: [
            "12 tortillas de maíz",
            "1 libra de carne molida",
            "2 papas cocidas y picadas",
            "2 zanahorias cocidas y picadas",
            "1 repollo picado finamente",
            "4 huevos duros picados",
            "Queso duro rallado",
            "Salsa de tomate",
            "Aceite para freír"
          ],
          instrucciones: [
            "Freír las tortillas en aceite calente hasta que estén crujientes.",
            "En un sartén, cocinar la carne molida hasta que esté dorada.",
            "Mezclar la carne con las papas y zanahorias picadas.",
            "Sobre cada tortilla frita, colocar una cama de repollo picado.",
            "Agregar la mezcla de carne y verduras.",
            "Cubrir con salsa de tomate, huevo picado y queso rallado.",
            "Servir inmediatamente."
          ],
          consejos: "Mantenga las tortillas fritas en un horno tibio para que conserven su textura crujiente hasta el momento de servir."
        }
      ],
      categorias: ["Plato Principal", "Sopa", "Bebida", "Postre", "Aperitivo"],
      dificultades: ["Fácil", "Media", "Alta"]
    }
  },
  computed: {
    recetasFiltradas() {
      return this.recetas.filter(receta => {
        const matchesSearch = this.searchQuery === '' || 
                             receta.nombre.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             receta.descripcion.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             receta.ingredientes.some(ing => ing.toLowerCase().includes(this.searchQuery.toLowerCase()));
        
        const matchesCategory = this.categoriaFiltro ? 
          receta.categoria === this.categoriaFiltro : true;
        
        const matchesDifficulty = this.dificultadFiltro ? 
          receta.dificultad === this.dificultadFiltro : true;
        
        return matchesSearch && matchesCategory && matchesDifficulty;
      });
    }
  },
  methods: {
    performSearch() {
      this.searchPerformed = this.searchQuery !== '' || this.categoriaFiltro !== '' || this.dificultadFiltro !== '';
    },
    verRecetaCompleta(receta) {
      this.recetaSeleccionada = receta;
    },
    cerrarModal() {
      this.recetaSeleccionada = null;
    },
    replaceImage(event) {
      event.target.src = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop';
    }
  }
}
</script>

<style scoped>
.recetas-view {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Encabezado */
.recetas-header {
  text-align: center;
  margin-bottom: 30px;
}

.recetas-header h1 {
  color: #005C34;
  margin-bottom: 10px;
  font-size: 2.5rem;
}

.recetas-header p {
  color: #666;
  font-size: 1.1rem;
  max-width: 700px;
  margin: 0 auto;
}

/* Filtros MEJORADOS - SOLO LA BARRA DE BÚSQUEDA */
.recetas-filtros {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 100px;
  margin-bottom: 25px;
  padding: 18px;
  background: #f8f8f8;
  border-radius: 12px;
}

.search-bar {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-bar i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #777;
  z-index: 2;
}

.search-bar input {
  width: 100%;
  padding: 12px 15px 12px 45px;
  border: 2px solid #ddd;
  border-radius: 25px;
  font-size: 1rem;
  outline: none;
  transition: all 0.3s ease;
  position: relative;
  background: white;
}

.search-bar input:focus {
  border-color: #27ae60;
  box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.2);
}

.filter-options {
  display: flex;
  gap: 12px;
}

.filter-options select {
  padding: 10px 15px;
  border-radius: 20px;
  border: 1px solid #ddd;
  background: white;
  min-width: 150px;
}

.results-info {
  margin-top: 15px;
  padding: 12px;
  background-color: #e8f5e9;
  border-radius: 6px;
  border-left: 4px solid #27ae60;
  width: 100%;
}

.results-info p {
  margin: 5px 0;
  color: #2c3e50;
  font-size: 0.9rem;
}

/* El resto de tus estilos se mantienen igual */
.view-toggle {
  display: flex;
  justify-content: center;
  margin-bottom: 25px;
  gap: 10px;
}

.view-toggle button {
  background: #f0f0f0;
  border: none;
  padding: 10px 20px;
  border-radius: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
}

.view-toggle button.active {
  background: #005C34;
  color: white;
}

.view-toggle button:hover:not(.active) {
  background: #e0e0e0;
}

/* Contenedor de recetas */
.recetas-container {
  display: grid;
  gap: 25px;
  margin-bottom: 40px;
}

.recetas-container.galeria {
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
}

.recetas-container.lista {
  grid-template-columns: 1fr;
}

/* Tarjetas de recetas */
.receta-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.receta-card.galeria {
  flex-direction: column;
}

.receta-card.lista {
  flex-direction: row;
  height: 250px;
}

.receta-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.receta-image {
  position: relative;
  height: 200px;
  overflow: hidden;
  flex-shrink: 0;
}

.receta-card.lista .receta-image {
  width: 300px;
  height: 100%;
}

.receta-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.receta-card:hover .receta-image img {
  transform: scale(1.05);
}

.receta-category {
  position: absolute;
  top: 15px;
  left: 15px;
  background: rgba(0, 92, 52, 0.85);
  color: white;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.receta-difficulty {
  position: absolute;
  top: 15px;
  right: 15px;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  color: white;
}

.receta-difficulty.fácil {
  background: rgba(76, 175, 80, 0.85);
}

.receta-difficulty.media {
  background: rgba(255, 152, 0, 0.85);
}

.receta-difficulty.alta {
  background: rgba(244, 67, 54, 0.85);
}

.receta-content {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.receta-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #222;
  margin: 0 0 15px 0;
  line-height: 1.3;
}

.receta-meta {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
  color: 555;
  font-size: 0.9rem;
}

.receta-meta span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.receta-description {
  color: #666;
  line-height: 1.5;
  margin-bottom: 15px;
  flex: 1;
}

.receta-ingredients {
  margin-bottom: 20px;
}

.receta-ingredients h4 {
  margin-bottom: 8px;
  color: #005C34;
  font-size: 1rem;
}

.receta-ingredients ul {
  padding-left: 20px;
  color: #555;
}

.receta-ingredients li {
  margin-bottom: 4px;
  font-size: 0.9rem;
}

.btn-view-recipe {
  background: #005C34;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: auto;
}

.btn-view-recipe:hover {
  background: #004026;
}

/* Modal de receta completa */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content.receta-modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 900px;
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

.receta-completa-header {
  margin-bottom: 25px;
  border-bottom: 2px solid #f0f0f0;
  padding-bottom: 15px;
}

.receta-completa-header h2 {
  color: #005C34;
  margin-bottom: 10px;
  font-size: 2rem;
}

.receta-meta-completa {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  color: #666;
}

.receta-meta-completa span {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: 20px;
  background: #f5f5f5;
  font-size: 0.9rem;
}

.receta-meta-completa .difficulty.fácil {
  background: #e8f5e9;
  color: #2e7d32;
}

.receta-meta-completa .difficulty.media {
  background: #fff3e0;
  color: #ef6c00;
}

.receta-meta-completa .difficulty.alta {
  background: #ffebee;
  color: #c62828;
}

.receta-completa-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
}

.receta-completa-image {
  border-radius: 12px;
  overflow: hidden;
}

.receta-completa-image img {
  width: 100%;
  height: auto;
  border-radius: 12px;
}

.receta-completa-details {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.ingredientes-completos h3, .instrucciones-completas h3, .receta-tips h3 {
  color: #005C34;
  margin-bottom: 15px;
  padding-bottom: 5px;
  border-bottom: 2px solid #f0f0f0;
}

.ingredientes-completos ul {
  padding-left: 20px;
  margin-bottom: 0;
}

.ingredientes-completos li {
  margin-bottom: 8px;
  line-height: 1.5;
}

.instrucciones-completas ol {
  padding-left: 20px;
}

.instrucciones-completas li {
  margin-bottom: 15px;
  line-height: 1.6;
}

.receta-tips {
  background: #f9f9f9;
  padding: 15px;
  border-radius: 8px;
  border-left: 4px solid #005C34;
}

.receta-tips p {
  margin: 0;
  line-height: 1.6;
  color: #555;
}

/* Responsive */
@media (max-width: 992px) {
  .recetas-filtros {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-options {
    justify-content: center;
  }
  
  .receta-completa-content {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .recetas-header h1 {
    font-size: 2rem;
  }
  
  .recetas-container.galeria {
    grid-template-columns: 1fr;
  }
  
  .receta-card.lista {
    flex-direction: column;
    height: auto;
  }
  
  .receta-card.lista .receta-image {
    width: 100%;
    height: 200px;
  }
  
  .filter-options {
    flex-direction: column;
  }
  
  .receta-meta-completa {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 576px) {
  .recetas-view {
    padding: 15px;
  }
  
  .recetas-header h1 {
    font-size: 1.8rem;
  }
  
  .receta-title {
    font-size: 1.2rem;
  }
  
  .receta-meta {
    flex-direction: column;
    gap: 8px;
  }
  
  .modal-content.receta-modal {
    padding: 20px 15px;
  }
  
  .receta-completa-header h2 {
    font-size: 1.6rem;
  }
}
</style>