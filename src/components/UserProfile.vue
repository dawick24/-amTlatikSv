<template>
  <div class="profile-modal" v-if="show" @click.self="close">
    <div class="profile-content" @click.stop>
      <span class="close-modal" @click="close">&times;</span>
      <h2 class="modal-title">Mi Perfil</h2>

      <!-- Sección de Foto de Perfil -->
      <div class="avatar-section">
        <div class="avatar-container">
          <img 
            v-if="userProfile.avatar" 
            :src="userProfile.avatar" 
            class="user-avatar"
          />
          <div v-else class="user-avatar placeholder">
            {{ getUserInitials }}
          </div>
        </div>
        
        <input 
          type="file" 
          ref="avatarInput"
          accept="image/*" 
          @change="handleAvatarUpload" 
          style="display: none"
        />
        <div class="avatar-buttons">
          <button @click="$refs.avatarInput.click()" class="btn-change-avatar">
            📷 Cambiar Foto
          </button>
          <button 
            v-if="userProfile.avatar" 
            @click="removeAvatar" 
            class="btn-remove-avatar"
          >
            ❌ Eliminar
          </button>
        </div>
        <p class="avatar-hint">Tamaño máximo: 1MB • Formatos: JPG, PNG</p>
      </div>

      <!-- Información del Usuario -->
      <div class="user-info">
        <div class="info-item">
          <label>Nombre:</label>
          <span>{{ userProfile.displayName || 'No establecido' }}</span>
        </div>
        <div class="info-item">
          <label>Email:</label>
          <span>{{ userProfile.email }}</span>
        </div>
        <div class="info-item">
          <label>Rol:</label>
          <span class="role-badge" :class="userProfile.role">
            {{ userProfile.role === 'admin' ? '👑 Administrador' : '👤 Usuario' }}
          </span>
        </div>
        <div class="info-item" v-if="userProfile.createdAt">
          <label>Miembro desde:</label>
          <span>{{ formatDate(userProfile.createdAt) }}</span>
        </div>
      </div>

      <!-- Progreso de Subida -->
      <div v-if="uploadProgress > 0" class="upload-progress">
        <p>Subiendo imagen: {{ uploadProgress }}%</p>
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
        </div>
      </div>

      <div class="profile-actions">
        <button @click="close" class="btn-close">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { 
  db, 
  auth 
} from '@/firebase'
import { doc, updateDoc, getDoc } from 'firebase/firestore'
import { updateProfile } from 'firebase/auth'

const props = defineProps({
  show: Boolean,
  user: Object
})

const emit = defineEmits(['close', 'profile-updated'])

// Datos del perfil
const userProfile = reactive({
  displayName: '',
  email: '',
  avatar: '',
  role: 'user',
  createdAt: null
})

const uploadProgress = ref(0)

// Referencia al input de archivo
const avatarInput = ref(null)

// Iniciales del usuario para avatar por defecto
const getUserInitials = computed(() => {
  if (!userProfile.displayName) return '👤'
  return userProfile.displayName
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

// Cargar datos del perfil
const loadUserProfile = async () => {
  if (!props.user) return
  
  try {
    // Cargar datos de Firestore
    const userDoc = await getDoc(doc(db, 'users', props.user.uid))
    if (userDoc.exists()) {
      const userData = userDoc.data()
      
      Object.assign(userProfile, {
        displayName: props.user.displayName || `${userData.nombre} ${userData.apellido}`,
        email: props.user.email,
        avatar: userData.avatar || '',
        role: userData.role || 'user',
        createdAt: userData.createdAt
      })
    }
  } catch (error) {
    console.error('Error cargando perfil:', error)
  }
}

// Convertir imagen a Base64
const convertToBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.readAsDataURL(file)
    reader.onload = () => resolve(reader.result)
    reader.onerror = error => reject(error)
  })
}

// Manejar subida de avatar
const handleAvatarUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validaciones
  if (!file.type.startsWith('image/')) {
    alert('Por favor selecciona una imagen válida (JPEG, PNG, etc.)')
    return
  }

  if (file.size > 1 * 1024 * 1024) {
    alert('La imagen debe ser menor a 1MB')
    return
  }

  try {
    uploadProgress.value = 10

    // Convertir a Base64
    const base64Image = await convertToBase64(file)
    uploadProgress.value = 60

    // Actualizar en Firestore
    await updateDoc(doc(db, 'users', props.user.uid), {
      avatar: base64Image
    })
    uploadProgress.value = 90

    // Actualizar perfil en Authentication (opcional)
    await updateProfile(auth.currentUser, {
      photoURL: base64Image
    })

    // Actualizar localmente
    userProfile.avatar = base64Image
    uploadProgress.value = 100

    // Emitir evento de actualización
    emit('profile-updated', { avatar: base64Image })

    // Reset progress después de 1 segundo
    setTimeout(() => {
      uploadProgress.value = 0
    }, 1000)

  } catch (error) {
    console.error('Error subiendo avatar:', error)
    alert('Error al subir la imagen')
    uploadProgress.value = 0
  }
}

// Eliminar avatar
const removeAvatar = async () => {
  if (!confirm('¿Estás seguro de que quieres eliminar tu foto de perfil?')) {
    return
  }

  try {
    // Eliminar de Firestore
    await updateDoc(doc(db, 'users', props.user.uid), {
      avatar: ''
    })

    // Eliminar de Authentication (opcional)
    await updateProfile(auth.currentUser, {
      photoURL: null
    })

    // Actualizar localmente
    userProfile.avatar = ''

    // Emitir evento de actualización
    emit('profile-updated', { avatar: '' })

    alert('Foto de perfil eliminada correctamente')

  } catch (error) {
    console.error('Error eliminando avatar:', error)
    alert('Error al eliminar la foto de perfil')
  }
}

// Formatear fecha
const formatDate = (timestamp) => {
  if (!timestamp) return 'N/A'
  const date = new Date(timestamp.seconds * 1000)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const close = () => {
  emit('close')
}

// Cargar perfil cuando se muestre el modal
watch(() => props.show, (newVal) => {
  if (newVal && props.user) {
    loadUserProfile()
  }
})
</script>

<style scoped>
.profile-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  box-sizing: border-box;
}

.profile-content {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  width: 90%;
  max-width: 450px;
  max-height: 80vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  margin: auto;
}

.close-modal {
  position: absolute;
  top: 1rem;
  right: 1rem;
  cursor: pointer;
  font-size: 1.5rem;
  color: #666;
  background: none;
  border: none;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background 0.3s ease;
  z-index: 1001;
}

.close-modal:hover {
  background: #f5f5f5;
}

.modal-title {
  color: #ff6f61;
  margin-bottom: 1.5rem;
  text-align: center;
  font-size: 1.5rem;
}

/* Sección Avatar */
.avatar-section {
  text-align: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.avatar-container {
  margin-bottom: 1rem;
}

.user-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #ff6f61;
  margin: 0 auto;
}

.user-avatar.placeholder {
  background: linear-gradient(135deg, #ff6f61, #ff8e76);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: bold;
  margin: 0 auto;
}

.avatar-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.btn-change-avatar {
  background: #ff6f61;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.3s ease;
}

.btn-change-avatar:hover {
  background: #e85b4e;
}

.btn-remove-avatar {
  background: #dc2626;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.3s ease;
}

.btn-remove-avatar:hover {
  background: #b91c1c;
}

.avatar-hint {
  color: #666;
  font-size: 0.75rem;
  margin: 0;
}

/* Información del Usuario */
.user-info {
  margin-bottom: 2rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
}

.info-item label {
  font-weight: 600;
  color: #374151;
  min-width: 120px;
}

.info-item span {
  color: #6b7280;
  flex: 1;
  text-align: right;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  display: inline-block;
  min-width: auto;
  width: auto;
  text-align: center;
  white-space: nowrap;
}

.role-badge.user {
  background: #e2e8f0;
  color: #475569;
}

.role-badge.admin {
  background: linear-gradient(135deg, #ff6f61, #ff8e76);
  color: white;
}

/* Progreso de Subida */
.upload-progress {
  text-align: center;
  margin: 1rem 0;
  padding: 1rem;
  background: #f1f5f9;
  border-radius: 8px;
}

.upload-progress p {
  margin: 0 0 0.5rem 0;
  color: #475569;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #ff6f61, #ff8e76);
  transition: width 0.3s ease;
}

/* Acciones */
.profile-actions {
  text-align: center;
}

.btn-close {
  background: #6b7280;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.3s ease;
}

.btn-close:hover {
  background: #4b5563;
}

/* Responsive */
@media (max-width: 768px) {
  .profile-modal {
    padding: 10px;
    align-items: flex-start;
    padding-top: 50px;
  }
  
  .profile-content {
    padding: 1.5rem;
    margin: 0;
    max-height: 85vh;
  }
  
  .avatar-buttons {
    flex-direction: column;
  }
  
  .info-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .info-item label {
    min-width: auto;
  }
  
  .info-item span {
    text-align: left;
  }
}

@media (max-height: 600px) {
  .profile-modal {
    align-items: flex-start;
    padding-top: 20px;
  }
  
  .profile-content {
    max-height: 90vh;
  }
}
</style>