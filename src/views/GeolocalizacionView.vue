<script setup>
import { onMounted, ref } from 'vue';

const map = ref('');
const center = ref({ lat: 13.4500, lng: -88.4500 });

async function initMap() {
  const { Map } = await google.maps.importLibrary("maps");
  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(
        (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
        () => reject()
      );
    });
    center.value = position;
  } catch {
    console.warn("No se pudo obtener ubicación. Usando la ubicación por defecto.");
  }
  map.value = new Map(document.getElementById("map"), {
    center: center.value,
    zoom: 15,
  });
}

onMounted(async () => {
  await initMap();
});
</script>

<template>
  <section
    style="display: flex; justify-content: center; align-items: center"
  >
    <h2>Mapa de locales con comida típica</h2>
  </section>

  <section>
    <div class="search-bar">
      <input 
        type="text" 
        v-model="query" 
        placeholder="Buscar lugares..." 
        @keyup.enter="searchPlaces"
        :disabled="!mapLoaded"
      />
      <button 
        type="button" 
        @click="searchPlaces"
        :disabled="!mapLoaded"
      >
        Buscar
      </button>
    </div>
    
    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>
    
    <div id="map"></div>
  </section>
</template>

<style scoped>
.search-bar {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
}

input {
  padding: 8px 12px;
  font-size: 16px;
  width: 300px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

button {
  padding: 8px 16px;
  font-size: 16px;
  cursor: pointer;
  background-color: #4285F4;
  color: white;
  border: none;
  border-radius: 4px;
  transition: background-color 0.3s;
}

button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

button:hover:not(:disabled) {
  background-color: #3367D6;
}

#map {
  height: 70vh;
  width: 100%;
  border-radius: 8px;
  border: 1px solid #ddd;
}


</style>