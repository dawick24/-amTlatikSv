<template>
  <div class="historia-container">
    <!-- Navegación con indicador visual activo -->
    <nav class="nav-container">
      <button 
        class="nav-button" 
        :class="{ active: activeSection === 'historia' }"
        @click="activeSection = 'historia'"
      >
        Historia
      </button>
    </nav>

    <!-- Contenido principal -->
    <div class="main-content">
      <!-- Sidebar de búsqueda -->
      <aside class="sidebar" aria-label="Barra de búsqueda de historias">
        <h3 class="search-title">Buscar historia</h3>
        <input
          type="text"
          v-model="searchTerm"
          class="search-input"
          placeholder="Buscar historia..."
          @input="filterHistorias"
          aria-label="Buscar historia"
        />
        <ul class="results-list" role="listbox" aria-label="Resultados de búsqueda">
          <li
            v-for="historia in filteredHistorias"
            :key="historia.id"
            @click="showDetail(historia.id)"
            class="result-item"
            role="option"
            :aria-selected="currentHistoria && currentHistoria.id === historia.id"
          >
            {{ historia.titulo }}
          </li>
          <li v-if="filteredHistorias.length === 0 && searchTerm.trim() !== ''" class="no-results">
            No se encontraron resultados para "{{ searchTerm }}"
          </li>
        </ul>
      </aside>

      <!-- Área de contenido -->
      <main class="content-area">
        <!-- Vista de listado -->
        <div v-if="!showDetailView" class="list-view" aria-live="polite">
          <h2 class="section-title">Platillos Tradicionales de El Salvador</h2>
          <p class="section-subtitle">Descubre la riqueza y tradición de la gastronomía salvadoreña</p>
          
          <div class="dishes-grid">
            <article
              v-for="platillo in platillos"
              :key="platillo.id"
              class="dish-card"
              @click="showDishHistory(platillo.id)"
              tabindex="0"
              @keyup.enter="showDishHistory(platillo.id)"
              role="button"
              :aria-label="`Ver historia de ${platillo.nombre}`"
            >
              <div class="dish-image" @click.stop>
                <img 
                  :src="platillo.imagen" 
                  :alt="`Imagen de ${platillo.nombre}`" 
                  loading="lazy"
                  @error="imageError"
                  @click.stop="openImageModal(platillo.imagen, platillo.nombre)"
                >
                <div class="image-overlay" @click.stop>
                  <button class="overlay-btn" @click.stop="showDishHistory(platillo.id)" aria-label="Ver historia completa">
                    Ver historia completa
                  </button>
                </div>
              </div>
              <div class="dish-content">
                <h3 class="dish-name">{{ platillo.nombre }}</h3>
                <p class="dish-description">{{ platillo.descripcionCorta }}</p>
                <div class="dish-meta">
                  <span class="origin">{{ platillo.origen }}</span>
                  <span class="read-time">{{ platillo.tiempoLectura }}</span>
                </div>
              </div>
            </article>
          </div>
        </div>

        <!-- Vista de detalle -->
        <transition name="fade-slide" mode="out-in">
          <section v-if="showDetailView" class="detail-view" aria-live="polite">
            <div class="detail-header">
              <button class="back-button" @click="goBackToList" aria-label="Volver al listado">
                <span class="arrow">←</span> Volver al listado
              </button>
            </div>
            
            <div class="detail-content">
              <aside class="detail-image-container" aria-hidden="false">
                <figure class="detail-image">
                  <img 
                    :src="currentImageSrc" 
                    :alt="currentHistoria ? currentHistoria.titulo : 'Imagen del platillo'"
                    @error="imageError"
                    @click="openImageModal(currentImageSrc, currentHistoria ? currentHistoria.titulo : '')"
                    tabindex="0"
                    @keyup.enter="openImageModal(currentImageSrc, currentHistoria ? currentHistoria.titulo : '')"
                  >
                </figure>
                <figcaption class="image-caption">
                  {{ currentHistoria ? (currentHistoria.titulo + (currentHistoria.caption ? ' - ' + currentHistoria.caption : '')) : '' }}
                </figcaption>
              </aside>
              
              <div class="detail-text">
                <h1 v-if="currentHistoria">{{ currentHistoria.titulo }}</h1>
                <div class="history-meta" v-if="currentHistoria">
                  <span class="origin">{{ currentHistoria.origen }}</span>
                  <span class="read-time">{{ currentHistoria.tiempoLectura }}</span>
                </div>
                
                <div class="history-content" v-if="currentHistoria">
                  <h2>Origen e Historia</h2>
                  <p v-for="(paragraph, index) in currentHistoria.descripcion.split('\n')" :key="index">
                    {{ paragraph }}
                  </p>
                  
                  <h2>Ingredientes Tradicionales</h2>
                  <p>{{ currentHistoria.ingredientes }}</p>
                  
                  <h2>Preparación</h2>
                  <p>{{ currentHistoria.preparacion }}</p>
                  
                  <h2>Significado Cultural</h2>
                  <p>{{ currentHistoria.significado }}</p>
                </div>
                
                <div v-if="currentHistoria && currentHistoria.curiosidades" class="fun-facts">
                  <h3>Datos Curiosos</h3>
                  <ul>
                    <li v-for="(curiosidad, index) in currentHistoria.curiosidades" :key="index">
                      {{ curiosidad }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </section>
        </transition>
      </main>
    </div>

    <!-- Modal de imagen (lightbox) -->
    <div v-if="imageModalVisible" class="image-modal" @click.self="closeImageModal" role="dialog" aria-modal="true">
      <button class="image-modal-close" @click="closeImageModal" aria-label="Cerrar imagen">✕</button>
      <img :src="imageModalSrc" :alt="imageModalAlt" @error="imageError" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'HistoriaView',
  data() {
    return {
      activeSection: 'historia',
      searchTerm: '',
      showDetailView: false,
      currentHistoria: null,
      // track last clicked platillo to choose image if historia lacks one
      lastClickedPlatillo: null,
      imageModalVisible: false,
      imageModalSrc: '',
      imageModalAlt: '',
      historias: [
        {
          id: 1,
          titulo: "Pupusas",
          imagen: "pupusas.jpeg",
          caption: "Plato tradicional salvadoreño",
          origen: "Origen: Prehispánico",
          tiempoLectura: "10 min de lectura",
          descripcion: `Las pupusas son el platillo más emblemático de El Salvador, con una historia que se remonta a más de dos mil años. Su origen se encuentra en las culturas indígenas pipiles, quienes habitaban el territorio que hoy conocemos como El Salvador. Los pipiles preparaban estas tortillas gruesas de maíz rellenas con diversos ingredientes locales como frijoles, chicharrón, ayote y hierbas como el chipilín y el loroco.

La palabra "pupusa" proviene del pipil "pupushahua", que significa "hinchado" o "abultado", haciendo referencia a su característica forma. Originalmente, las pupusas se cocinaban en comales de barro sobre fuego de leña, método que aún se utiliza en muchas regiones rurales del país y que aporta un sabor único imposible de replicar con métodos modernos.

Con la llegada de los españoles, se introdujeron nuevos ingredientes como el queso y el cerdo, que se incorporaron a la gastronomía local y enriquecieron las variedades de pupusas. Durante el período colonial, las pupusas se convirtieron en un alimento básico para las comunidades indígenas y mestizas, representando una fusión de tradiciones culinarias.

En el siglo XX, con la migración interna hacia las ciudades, las pupusas se popularizaron en todo el país. Aparecieron las "pupuserías", establecimientos dedicados exclusivamente a la preparación y venta de este platillo. Hoy existen más de 20 variedades de pupusas, siendo las más populares las de queso, frijol, chicharrón, revueltas y loroco.

En 2005, la Asamblea Legislativa de El Salvador declaró las pupusas como "Patrimonio Cultural de la Nación" y estableció el segundo domingo de noviembre como "Día Nacional de las Pupusas". Este platillo no solo es el más representativo de la gastronomía salvadoreña, sino también un símbolo de identidad nacional que une a todos los salvadoreños, tanto dentro como fuera del país.`,
          ingredientes: "Maíz nixtamalizado, agua, sal, y diversos rellenos como queso, frijoles refritos, chicharrón molido, loroco, ayote, chipilín o revueltos.",
          preparacion: "La masa de maíz se prepara con maíz nixtamalizado molido. Se toma una porción de masa, se hace un hueco en el centro donde se coloca el relleno, se sella cuidadosamente y se aplana formando un disco que se cocina en un comal hasta dorar por ambos lados.",
          significado: "Las pupusas representan la herencia indígena de El Salvador y son un elemento unificador de la cultura salvadoreña. Son el platillo nacional por excelencia y se consumen en todas las regiones del país, en todas las clases sociales y en cualquier momento del día. Su preparación y consumo son actos que refuerzan los lazos familiares y comunitarios.",
          curiosidades: [
            "El Día Nacional de la Pupusa se celebra cada segundo domingo de noviembre",
            "Existen más de 20 variedades de pupusas según sus rellenos",
            "El récord mundial de la pupusa más grande fue establecido en El Salvador en 2019, midiendo 3.15 metros de diámetro",
            "En 2005, las pupusas fueron declaradas Patrimonio Cultural de la Nación",
            "Las pupuserías son negocios familiares que han pasado de generación en generación"
          ]
        },
        {
          id: 2,
          titulo: "Montucas",
          imagen: "montacua.jpg",
          caption: "Tamal de elote salvadoreño",
          origen: "Origen: Prehispánico",
          tiempoLectura: "8 min de lectura",
          descripcion: `Las montucas, también conocidas como tamales de elote, son una delicia tradicional salvadoreña con profundas raíces en la cultura mesoamericana. Su origen se remonta a las prácticas culinarias de los pueblos indígenas que habitaban la región, quienes desarrollaron técnicas para aprovechar el maíz en sus diferentes estados de maduración.

El nombre "montuca" proviene de la palabra náhuatl "montúc" que significa "envuelto", haciendo referencia a su método de preparación. A diferencia de los tamales comunes que utilizan masa de maíz nixtamalizado, las montucas se elaboran con maíz tierno (elote) molido, lo que les confiere una textura más suave y un sabor ligeramente dulce.

Tradicionalmente, las montucas se preparaban durante la temporada de cosecha del maíz, cuando los elotes están en su punto perfecto de maduración. Esta temporalidad las convertía en un alimento especial, asociado con celebraciones y festividades familiares. Con el tiempo, su preparación se extendió a lo largo de todo el año, pero manteniendo su carácter festivo.

La preparación de montucas es un proceso comunitario que involucra a varias generaciones de mujeres. Las abuelas enseñan a sus hijas y nietas los secretos para lograr la textura perfecta, el punto exacto de dulzura y la técnica adecuada para envolverlas en hojas de maíz. Este traspaso de conocimiento culinario ha permitido que la tradición se mantenga viva por siglos.

En la actualidad, las montucas son consideradas un manjar representativo de la cocina salvadoreña. Se consumen como plato principal o como acompañamiento en comidas especiales. Aunque han surgido variaciones modernas con ingredientes adicionales, la receta tradicional sigue siendo la más valorada por los conocedores de la gastronomía local.`,
          ingredientes: "Elotes tiernos, mantequilla o margarina, sal, azúcar (opcional), leche o crema, y ocasionalmente trozos de pollo o cerdo. Se envuelven en hojas de maíz o de plátano.",
          preparacion: "Los granos de elote se desgranan y muelen para obtener una pasta. Esta pasta se mezcla con los demás ingredientes y se cocina a fuego lento hasta espesar. Luego se vierte sobre hojas de maíz previamente preparadas, se envuelven y se cuecen al vapor durante aproximadamente una hora.",
          significado: "Las montucas representan la conexión con los ciclos agrícolas y la herencia prehispánica de El Salvador. Son un ejemplo de cómo los sabores tradicionales se mantienen vigentes a través de las generaciones, adaptándose pero sin perder su esencia. Su preparación y consumo son actos que refuerzan la identidad cultural y los lazos familiares.",
          curiosidades: [
            "Las montucas son especialmente populares durante la temporada de lluvias, cuando el maíz está en su punto ideal",
            "En algunas regiones se les conoce como 'tamales de elote'",
            "Existen versiones saladas y dulces, según los ingredientes adicionales",
            "La técnica de envoltura varía según la región del país",
            "Tradicionalmente se servían en las fiestas patronales de los pueblos"
          ]
        },
        {
          id: 3,
          titulo: "Tamales Pisques",
          imagen: "pisques.png",
          caption: "Tamal de frijol salvadoreño",
          origen: "Origen: Prehispánico",
          tiempoLectura: "9 min de lectura",
          descripcion: `Los tamales pisques son una variedad única de tamal salvadoreño que se caracteriza por su relleno de frijol rojo sazonado. Su nombre proviene del náhuatl "pixqui", que significa "desgranar" o "desmenuzar", haciendo referencia al proceso de preparación del frijol que se desmenuza para crear el relleno.

Estos tamales tienen sus raíces en las tradiciones culinarias prehispánicas, donde el maíz y el frijol constituían la base de la alimentación mesoamericana. Los pueblos indígenas desarrollaron técnicas para combinar estos dos ingredientes fundamentales en formas creativas y nutritivas, dando origen a platillos como los tamales pisques.

A diferencia de otros tamales que pueden contener carnes, los tamales pisques son tradicionalmente vegetarianos, lo que los convierte en una opción importante durante la Cuaresma y otras festividades religiosas donde se restringe el consumo de carne. Esta característica también refleja cómo las comunidades indígenas aprovechaban los recursos disponibles de manera sostenible.

La preparación de tamales pisques es un ritual que involucra a toda la familia. Las mujeres se reúnen para desgranar el maíz, preparar la masa, cocinar el frijol y finalmente armar los tamales, que se envuelven cuidadosamente en hojas de plátano. Este proceso no solo garantiza la transmisión de las recetas tradicionales, sino que también fortalece los lazos comunitarios.

Hoy en día, los tamales pisques siguen siendo un elemento central en las celebraciones familiares y religiosas. Representan la herencia indígena de El Salvador y son un recordatorio de la ingeniosidad culinaria que ha permitido que estas tradiciones perduren por siglos.`,
          ingredientes: "Maíz nixtamalizado, frijoles rojos, aceite o manteca, cebolla, chile verde, sal, hojas de plátano para envolver.",
          preparacion: "Se prepara una masa de maíz nixtamalizado molido. Por separado, se cocinan los frijoles hasta que estén blandos, se fríen con cebolla y chile, y se machacan para formar una pasta. Se extiende la masa sobre hojas de plátano, se agrega el relleno de frijol, se envuelve y se cuece al vapor durante 1-2 horas.",
          significado: "Los tamales pisques representan la fusión de los dos ingredientes básicos de la dieta mesoamericana: el maíz y el frijol. Son un símbolo de la cocina vegetariana tradicional y de la observancia religiosa. Su preparación en comunidad refuerza los lazos familiares y la transmisión intergeneracional de conocimientos culinarios.",
          curiosidades: [
            "Son especialmente populares durante la Semana Santa y la Cuaresma",
            "En algunas regiones se les conoce como 'tamales de viaje' porque se transportaban fácilmente",
            "La hoja de plátano no se come, pero imparte un sabor característico al tamal",
            "Existen variantes con pequeños trozos de queso ou loroco",
            "Tradicionalmente se servían como alimento para los viajeros"
          ]
        },
        {
          id: 4,
          titulo: "Quesadilla Salvadoreña",
          imagen: "quesadilla.png",
          caption: "Pan dulce con queso salvadoreño",
          origen: "Origen: Colonial",
          tiempoLectura: "7 min de lectura",
          descripcion: `La quesadilla salvadoreña es un pan dulce que poco tiene que ver con la quesadilla mexicana. Su nombre proviene del ingrediente principal, el queso, que se incorpora a la masa junto con otros productos lácteos como la crema y la mantequilla. Este delicioso pan es un ejemplo de la fusión entre las técnicas europeas de repostería y los ingredientes locales.

Durante la época colonial, las órdenes religiosas introdujeron en El Salvador las técnicas de panadería y repostería europeas. Los conventos y monasterios se convirtieron en centros de producción de dulces y panes, adaptando las recetas europeas a los ingredientes disponibles localmente. La quesadilla salvadoreña surgió como una de estas adaptaciones, utilizando el queso fresco local en lugar de los quesos europeos.

A lo largo del siglo XIX, con la independencia y la consolidación de la república, la quesadilla salvadoreña se popularizó más allá de los círculos religiosos. Las panaderías familiares comenzaron a producirla, y cada familia desarrolló su propia variante de la receta, transmitida de generación en generación.

La quesadilla salvadoreña se caracteriza por su textura esponjosa y ligeramente húmeda, con un sabor equilibrado entre lo dulce y lo salado. A diferencia de otros panes dulces, contiene queso rallado en la masa, lo que le da una textura única y un sabor distintivo. Tradicionalmente se hornea en moldes redondos y se sirve en rebanadas.

Hoy en día, la quesadilla salvadoreña es un elemento indispensable en las celebraciones familiares, especialmente en cumpleaños, bautizos y reuniones sociales. Se consume en el desayuno o la merienda, acompañada de café ou chocolate caliente, y representa la herencia colonial y la creatividad culinaria salvadoreña.`,
          ingredientes: "Harina de trigo, huevos, mantequilla, queso duro rallado (como parmesano o similar), azúcar, crema agria o crema salvadoreña, levadura en polvo, sal, ajonjolí para decorar.",
          preparacion: "Se baten la mantequilla y el azúcar hasta cremar, se añaden los huevos uno a uno, luego la crema y finalmente la harina mezclada con la levadura y la sal. Se incorpora el queso rallado, se vierte la masa en un molde redondo engrasado, se decora con ajonjolí y se hornea a 180°C durante 30-40 minutos.",
          significado: "La quesadilla salvadoreña representa la fusión de tradiciones culinarias europeas e indígenas. Es un símbolo de las celebraciones familiares y la hospitalidad salvadoreña. Su presencia en eventos sociales refleja la importancia de la repostería en la cultura salvadoreña y la capacidad de adaptar recetas extranjeras a los ingredientes locales.",
          curiosidades: [
            "No tiene relación con la quesadilla mexicana, que es una tortilla rellena de queso",
            "Es tradicionalmente consumida durante el desayuno o la merienda",
            "Cada región de El Salvador tiene su variante particular",
            "Algunas versiones incluyen coco rallado o piña",
            "Es uno de los pocos panes salvadoreños que contiene queso en la masa"
          ]
        },
        {
          id: 5,
          titulo: "Enchiladas Salvadoreñas",
          imagen: "enchilada.png",
          caption: "Tostadas salvadoreñas con diversos toppings",
          origen: "Origen: Prehispánico con influencia colonial",
          tiempoLectura: "8 min de lectura",
          descripcion: `Las enchiladas salvadoreñas son un platillo vibrante y colorido que poco tiene que ver con las enchiladas mexicanas. Consisten en una tortilla de maíz frita crujiente, sobre la cual se disponen diversos ingredientes como carne molida, huevo duro, repollo curtido, salsa de tomate y queso duro rallado. Esta combinación de sabores y texturas las convierte en un platillo festivo y popular.

El origen de las enchiladas salvadoreñas se remonta a las prácticas culinarias prehispánicas de tostar tortillas de maíz y complementarlas con ingredientes disponibles. Con la llegada de los españoles, se incorporaron nuevos elementos como la carne de res molida, los huevos y las técnicas de encurtido, dando forma al platillo que conocemos hoy.

Durante el siglo XIX, las enchiladas se popularizaron como comida callejera, especialmente en mercados y festividades. Los vendedores ambulantes las preparaban al momento, permitiendo a los clientes personalizar sus toppings según sus preferencias. Esta tradición continúa hoy en día, donde es común encontrar puestos de enchiladas en ferias y eventos públicos.

La preparación de las enchiladas salvadoreñas es un ejercicio de equilibrio y armonía de sabores. La base crujiente de la tortilla contrasta con la suavidad de los demás ingredientes, mientras que el curtido de repollo aporta un toque ácido que balancea la riqueza de la carne y los huevos. La salsa de tomate casera y el queso rallado completan esta explosión de sabores.

Hoy en día, las enchiladas salvadoreñas son un platillo emblemático de la comida rápida tradicional. Se consumen como refrigerio o comida ligera, y son especialmente populares entre los jóvenes. Representan la evolución de la cocina salvadoreña y su capacidad para adaptar influencias externas creando platillos únicos y distintivos.`,
          ingredientes: "Tortillas de maíz, carne molida de res, huevos, repollo, zanahoria, cebolla, chile verde, tomates, aceite, sal, queso duro rallado, perejil.",
          preparacion: "Se fríen las tortillas en aceite hasta que estén crujientes. Por separado, se cocina la carne molida con especias, se cocinan los huevos duros, se prepara un curtido de repollo y zanahoria, y se hace una salsa de tomate. Para servir, se coloca la tortilla frita como base y se cubre con todos los ingredientes.",
          significado: "Las enchiladas salvadoreñas representan la fusión de tradiciones culinarias indígenas y europeas. Son un símbolo de la comida callejera y las festividades populares. Su carácter personalizable refleja la diversidad y creatividad de la gastronomía salvadoreña, así como su capacidad para reinventar platillos con identidad propia.",
          curiosidades: [
            "No tienen relación con las enchiladas mexicanas, que son tortillas rellenas bañadas en salsa",
            "Son consideradas 'comida rápida' tradicional salvadoreña",
            "Cada puesto callejero tiene su receta secreta para la salsa de tomate",
            "Se pueden personalizar con ingredientes adicionales como aguacate o chorizo",
            "Son especialmente populares durante las fiestas patronales"
          ]
        },
        {
          id: 6,
          titulo: "Mariscada",
          imagen: "mariscada.png",
          caption: "Sopa de mariscos salvadoreña",
          origen: "Origen: Costeño con influencia internacional",
          tiempoLectura: "9 min de lectura",
          descripcion: `La mariscada es una sustanciosa sopa de mariscos que refleja la riqueza de las costas salvadoreñas. Aunque El Salvador tiene una costa relativamente corta comparada con otros países centroamericanos, su tradición marinera ha dado lugar a platillos como la mariscada, que combina diversos frutos del mar en un caldo sabroso y aromático.

El origen de la mariscada se remonta a las comunidades pesqueras de la costa salvadoreña, donde los pescadores preparaban caldos con las capturas del día. Con el tiempo, estas preparaciones sencillas se enriquecieron con influencias de la cocina española (especialmente de regiones como Galicia) y de otras tradiciones culinarias costeras.

Durante el siglo XX, con la mejora de las vías de comunicación y la refrigeración, los mariscos se hicieron más accesibles en todo el país. La mariscada dejó de ser un platillo exclusivo de las comunidades costeras y se popularizó en restaurantes y hogares del interior, adaptándose a los ingredientes disponibles y los gustos locales.

La mariscada salvadoreña se caracteriza por su variedad de ingredientes marinos, que pueden incluir pescado, camarones, calamares, mejillones, almejas y cangrejos. Estos se cocinan en un caldo perfumado con ajo, cebolla, cilantro y especias, y se espesa ligeramente con verduras como tomate, chile dulce y yuca. El resultado es un platillo complejo y reconfortante.

Hoy en día, la mariscada es considerada un platillo especial, que se sirve en ocasiones festivas y reuniones familiares importantes. Es especialmente popular durante la Semana Santa, cuando muchas familias salvadoreñas sustituyen la carne por pescados y mariscos. Representa la conexión de El Salvador con el mar y su capacidad para incorporar influencias culinarias globales.`,
          ingredientes: "Variedad de mariscos (pescado, camarones, calamares, mejillones, almejas, cangrejo), ajo, cebolla, tomate, chile dulce, cilantro, culantro, yuca, elote, plátano verde, aceite, sal, pimienta, agua ou caldo de pescado.",
          preparacion: "Se sofríen el ajo, la cebolla y el tomate picados. Se añaden las especias y el caldo, y se agregan las verduras duras como la yuca. Cuando estas están casi cocidas, se añaden los mariscos según su tiempo de cocción (empezando por los que requieren más tiempo). Finalmente, se agregan las hierbas y se ajustan los sabores.",
          significado: "La mariscada representa la riqueza del litoral salvadoreño y la herencia de las comunidades pesqueras. Es un símbolo de celebración y abundancia, que reúne a familias y amigos alrededor de la mesa. Su preparación refleja la diversidad de la cocina salvadoreña y su capacidad para integrar ingredientes y técnicas de diversas tradiciones culinarias.",
          curiosidades: [
            "Es especialmente popular durante la Semana Santa y las festividades navideñas",
            "Cada familia costera tiene su versión particular de la receta",
            "Se suele servir con tortillas o pan francés para acompañar el caldo",
            "En algunas versiones se añade leche de coco para dar cremosidad",
            "Es considerada un platillo 'de fiesta' por su relativo costo elevado"
          ]
        }
      ],
      platillos: [
        {
          id: 1,
          nombre: "Pupusas",
          imagen: "pupusas.jpeg",
          descripcionCorta: "Tortilla gruesa de maíz rellena con queso, chicharrón, frijoles o loroco. Considerado el platillo nacional de El Salvador.",
          origen: "Origen prehispánico",
          tiempoLectura: "10 min",
          historiaId: 1
        },
        {
          id: 2,
          nombre: "Montucas",
          imagen: "montacua.jpg",
          descripcionCorta: "Tamal de elote tierno, dulce y suave. Tradicionalmente preparado durante la temporada de cosecha.",
          origen: "Origen prehispánico",
          tiempoLectura: "8 min",
          historiaId: 2
        },
        {
          id: 3,
          nombre: "Tamales Pisques",
          imagen: "pisques.png",
          descripcionCorta: "Tamales de maíz rellenos de frijoles cocidos y salsa de tomate. Preparados tradicionalmente para ocasiones especiales.",
          origen: "Origen prehispánico",
          tiempoLectura: "9 min",
          historiaId: 3
        },
        {
          id: 4,
          nombre: "Quesadilla Salvadoreña",
          imagen: "quesadilla.png",
          descripcionCorta: "Pan dulce con queso, no relacionado con la quesadilla mexicana. Postre tradicional con influencia española.",
          origen: "Origen colonial",
          tiempoLectura: "7 min",
          historiaId: 4
        },
        {
          id: 5,
          nombre: "Enchiladas Salvadoreñas",
          imagen: "enchilada.png",
          descripcionCorta: "Tortillas de maíz fritas topped con carne, huevo, verduras y salsa. Diferentes a las enchiladas mexicanas.",
          origen: "Fusión prehispánica-colonial",
          tiempoLectura: "8 min",
          historiaId: 5
        },
        {
          id: 6,
          nombre: "Mariscada",
          imagen: "mariscada.png",
          descripcionCorta: "Mezcla de mariscos en salsa criolla. Platillo costeño que refleja la influencia de la cocina mediterránea.",
          origen: "Origen costeño",
          tiempoLectura: "9 min",
          historiaId: 6
        }
      ]
    }
  },
  computed: {
    filteredHistorias() {
      if (!this.searchTerm.trim()) return this.historias;
      
      const term = this.searchTerm.toLowerCase();
      return this.historias.filter(h => 
        h.titulo.toLowerCase().includes(term) || 
        (h.descripcion && h.descripcion.toLowerCase().includes(term))
      );
    },
    // image to show in detail: prefer historia.imagen, else platillo clicked image
    currentImageSrc() {
      if (this.currentHistoria && this.currentHistoria.imagen) return this.currentHistoria.imagen;
      if (this.lastClickedPlatillo && this.lastClickedPlatillo.imagen) return this.lastClickedPlatillo.imagen;
      // placeholder SVG data URL (gray box) as fallback
      return this.placeholderDataUrl;
    },
    placeholderDataUrl() {
      const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='800' height='450'><rect width='100%' height='100%' fill='#efefef'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='#999' font-family='Segoe UI,Arial' font-size='24'>Imagen no disponible</text></svg>`;
      return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
    }
  },
  methods: {
    showDetail(id) {
      const historia = this.historias.find(h => h.id === id);
      if (historia) {
        this.currentHistoria = historia;
        this.showDetailView = true;
        this.lastClickedPlatillo = null;
        // ensure top of view
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },
    showDishHistory(dishId) {
      const dish = this.platillos.find(p => p.id === dishId);
      if (!dish) return;
      this.lastClickedPlatillo = dish;
      if (dish.historiaId) {
        this.showDetail(dish.historiaId);
      } else {
        // If no historia linked, create a minimal currentHistoria from platillo
        this.currentHistoria = {
          id: `p-${dish.id}`,
          titulo: dish.nombre,
          imagen: dish.imagen,
          caption: '',
          origen: dish.origen || '',
          tiempoLectura: dish.tiempoLectura || '',
          descripcion: dish.descripcionCorta || '',
          ingredientes: '',
          preparacion: '',
          significado: '',
          curiosidades: []
        };
        this.showDetailView = true;
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    goBackToList() {
      this.showDetailView = false;
      this.currentHistoria = null;
      this.lastClickedPlatillo = null;
    },
    filterHistorias() {
      // handled by computed property
    },
    // lightbox
    openImageModal(src, alt = '') {
      this.imageModalSrc = src || this.currentImageSrc;
      this.imageModalAlt = alt || (this.currentHistoria ? this.currentHistoria.titulo : '');
      this.imageModalVisible = true;
    },
    closeImageModal() {
      this.imageModalVisible = false;
      this.imageModalSrc = '';
      this.imageModalAlt = '';
    },
    imageError(event) {
      // reemplaza por placeholder incrustado
      event.target.src = this.placeholderDataUrl;
    },
    // close modals on ESC
    onKeydown(e) {
      if (e.key === 'Escape') {
        if (this.imageModalVisible) this.closeImageModal();
        else if (this.showDetailView) this.goBackToList();
      }
    }
  },
  mounted() {
    window.addEventListener('keydown', this.onKeydown);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.onKeydown);
  }
};
</script>

<style scoped>
.historia-container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
  background: #fff0eb;
}

/* Navegación */
.nav-container {
  display: flex;
  border-bottom: 2px solid #e0e0e0;
  margin-bottom: 30px;
}

.nav-button {
  background: none;
  border: none;
  padding: 15px 25px;
  font-size: 1.1rem;
  font-weight: 500;
  color: #777;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.nav-button:hover {
  color: #ff6f61;
}

.nav-button.active {
  color: #ff6f61;
  font-weight: 600;
}

.nav-button.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 3px;
  background-color: #ff6f61;
  border-radius: 3px 3px 0 0;
}

/* Layout principal */
.main-content {
  display: flex;
  gap: 30px;
}

/* Sidebar */
.sidebar {
  flex: 0 0 300px;
  background-color: #fff;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  height: fit-content;
}

.search-title {
  color: #ff6f61;
  font-weight: 600;
  margin-bottom: 15px;
  font-size: 1.25rem;
}

.search-input {
  width: 90%;
  padding: 12px 18px;
  border: 2px solid #e0e0e0;
  border-radius: 25px;
  font-size: 1rem;
  outline: none;
  transition: all 0.3s ease;
  margin-bottom: 20px;
}

.search-input:focus {
  border-color: #ff6f61;
  box-shadow: 0 0 0 3px rgba(255, 111, 97, 0.15);
}

.results-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.result-item {
  padding: 12px 15px;
  border-radius: 8px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
}

.result-item:hover {
  background-color: #f9f9f9;
  border-left-color: #ff6f61;
  transform: translateX(5px);
}

.no-results {
  padding: 15px;
  color: #777;
  font-style: italic;
  text-align: center;
}

/* Área de contenido */
.content-area {
  flex: 1;
}

/* Vista de listado */
.section-title {
  color: #ff6f61;
  font-size: 2.2rem;
  margin-bottom: 10px;
  text-align: center;
}

.section-subtitle {
  color: #7f8c8d;
  font-size: 1.2rem;
  text-align: center;
  margin-bottom: 40px;
}

.dishes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 25px;
  margin-top: 20px;
}

.dish-card {
  background-color: white;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.3s ease;
  cursor: pointer;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.dish-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.dish-image {
  width: 100%;
  height: 200px;
  overflow: hidden;
  position: relative;
  background: #f3f3f3;
}

.dish-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
  display: block;
  cursor: zoom-in;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.25s ease;
  pointer-events: none;
}

.overlay-btn {
  pointer-events: auto;
  color: white;
  font-weight: 600;
  text-align: center;
  padding: 8px 16px;
  background: rgba(255, 111, 97, 0.95);
  border: none;
  border-radius: 20px;
  cursor: pointer;
}

.dish-card:hover .dish-image img {
  transform: scale(1.05);
}

.dish-card:hover .image-overlay {
  opacity: 1;
}

.dish-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.dish-name {
  font-size: 1.3rem;
  font-weight: 600;
  color: #ff6f61;
  margin-bottom: 8px;
}

.dish-description {
  font-size: 1rem;
  color: #555;
  line-height: 1.5;
  margin-bottom: 10px;
  flex: 1;
}

.dish-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #888;
}

/* Vista de detalle */
.detail-view {
  background: white;
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.detail-header {
  padding: 20px 30px;
  border-bottom: 1px solid #eee;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #be5959;
  color: #f7f0f0;
  padding: 10px 20px;
  border-radius: 25px;
  font-weight: 500;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-button:hover {
  background: #931818;
  transform: translateX(-5px);
}

.arrow {
  font-size: 1.2rem;
}

.detail-content {
  padding: 30px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: start;
}

.detail-image-container {
  position: sticky;
  top: 30px;
}

.detail-image {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  margin-bottom: 15px;
}

.detail-image img {
  width: 100%;
  height: auto;
  display: block;
  cursor: zoom-in;
}

.image-caption {
  text-align: center;
  font-style: italic;
  color: #666;
  font-size: 0.9rem;
}

.detail-text h1 {
  color: #ff6f61;
  margin-bottom: 15px;
  font-size: 2.2rem;
  border-bottom: 2px solid #ffe2dc;
  padding-bottom: 10px;
}

.history-meta {
  display: flex;
  gap: 15px;
  margin-bottom: 25px;
  font-size: 0.9rem;
  color: #666;
}

.history-content h2 {
  color: #ff6f61;
  margin: 25px 0 15px;
  font-size: 1.5rem;
  border-left: 4px solid #ff6f61;
  padding-left: 10px;
}

.history-content p {
  font-size: 1.1rem;
  line-height: 1.7;
  margin-bottom: 20px;
  color: #444;
  text-align: justify;
}

.fun-facts {
  margin-top: 30px;
  padding: 25px;
  background-color: #fff9f8;
  border-radius: 12px;
  border-left: 4px solid #ff6f61;
}

.fun-facts h3 {
  color: #ff6f61;
  margin-bottom: 15px;
  font-size: 1.3rem;
}

.fun-facts ul {
  padding-left: 20px;
}

.fun-facts li {
  margin-bottom: 10px;
  line-height: 1.5;
  position: relative;
}

.fun-facts li::before {
  content: "•";
  color: #ff6f61;
  font-weight: bold;
  display: inline-block;
  width: 1em;
  margin-left: -1em;
}

/* Image modal */
.image-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1200;
  padding: 20px;
}

.image-modal img {
  max-width: 95%;
  max-height: 90vh;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.image-modal-close {
  position: fixed;
  top: 18px;
  right: 18px;
  background: #ff6f61;
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 18px;
  cursor: pointer;
  z-index: 1300;
}

/* Transiciones */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Responsive */
@media (max-width: 1024px) {
  .main-content {
    flex-direction: column;
  }
  
  .sidebar {
    flex: 1;
    margin-bottom: 30px;
  }
}

@media (max-width: 768px) {
  .detail-content {
    grid-template-columns: 1fr;
    gap: 30px;
  }
  
  .dishes-grid {
    grid-template-columns: 1fr;
  }
  
  .section-title {
    font-size: 1.8rem;
  }
  
  .section-subtitle {
    font-size: 1.1rem;
  }
  
  .detail-image-container {
    position: static;
  }
}

@media (max-width: 480px) {
  .historia-container {
    padding: 15px;
  }
  
  .detail-content,
  .detail-header {
    padding: 20px;
  }
  
  .detail-text h1 {
    font-size: 1.8rem;
  }
  
  .history-content h2 {
    font-size: 1.3rem;
  }
}
</style>