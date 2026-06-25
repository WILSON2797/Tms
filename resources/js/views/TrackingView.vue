<template>
  <div class="tracking-view">
    <!-- Data Table of Orders -->
    <DataTable 
      :columns="columns" 
      :data="orders" 
      :loading="loading" 
      empty-text="Belum ada data shipment order."
      title="Live Shipment Tracking"
      subtitle="Daftar seluruh shipment order. Klik ikon peta pada kolom aksi untuk melacak posisi pengiriman dan lini masa perjalanan secara real-time."
    >
      <template #cell(order_number)="{ value }">
        <span class="fw-bold text-gray-900">{{ value }}</span>
      </template>
      <template #cell(job_number)="{ value }">
        <span class="text-info fw-bold">{{ value }}</span>
      </template>
      <template #cell(trip_number)="{ row }">
        <span v-if="row.trip" class="fw-medium text-success">{{ row.trip.trip_number }}</span>
        <span v-else class="text-muted">-</span>
      </template>
      <template #cell(driver)="{ row }">
        <span v-if="row.trip && row.trip.driver" class="text-gray-900">{{ row.trip.driver.driver_name }}</span>
        <span v-else class="text-muted">-</span>
      </template>

      <template #cell(origin_city)="{ value }">
        <span class="text-gray-900">{{ value }}</span>
      </template>

      <template #cell(destination_city)="{ value }">
        <span class="text-gray-900 fw-semibold">{{ value }}</span>
      </template>

      <template #cell(status)="{ value }">
        <span class="badge px-2 py-1" :class="getStatusBadgeClass(value)">
          {{ value }}
        </span>
      </template>

      <template #cell(actions)="{ row }">
        <button 
          type="button" 
          class="btn btn-sm btn-outline-info d-flex align-items-center gap-1 border-0"
          @click="trackOrder(row)"
          title="Lacak Lokasi & Lini Masa"
        >
          <i class="bi bi-geo-alt-fill fs-5"></i>
        </button>
      </template>
    </DataTable>

    <!-- Live Tracking Map & Timeline Modal (Side-by-Side) -->
    <Teleport to="body">
      <div 
        v-if="showModal" 
        class="modal-backdrop-custom d-flex align-items-center justify-content-center"
        @click.self="closeModal"
      >
        <div class="modal-dialog-custom bg-dark-card border-card p-4 rounded-4 shadow-lg text-gray-900">
          <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary-custom pb-2">
            <h5 class="fw-bold text-gray-900 mb-0">
              <i class="bi bi-geo-alt text-primary me-2"></i>
              Live Tracking - {{ selectedOrder?.order_number }} ({{ selectedOrder?.job_number }})
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <div v-if="modalLoading" class="text-center py-5">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <p class="text-muted small">Memuat data pelacakan...</p>
          </div>

          <div v-else>
            <!-- Driver GPS Signal / Online Status Bar -->
            <div v-if="selectedOrder?.trip" class="mb-3 p-2 px-3 rounded bg-dark-custom border border-secondary-custom d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 13px;">
              <div class="d-flex align-items-center gap-3">
                <span class="d-flex align-items-center gap-1">
                  <i class="bi bi-person-fill text-muted me-1"></i>Supir: <strong class="text-gray-900">{{ selectedOrder.trip.driver?.driver_name || '-' }}</strong>
                </span>
                <span class="text-muted">|</span>
                <span class="d-flex align-items-center gap-1">
                  <i class="bi bi-truck text-muted me-1"></i>Plat: <strong class="text-gray-900">{{ selectedOrder.trip.vehicle?.vehicle_no || '-' }}</strong>
                </span>
              </div>
              
              <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                  <span class="legend-dot" :class="getDriverStatus(selectedOrder.trip).badgeClass" style="width: 8px; height: 8px; margin-right: 0;"></span>
                  <span class="fw-bold" :class="getDriverStatus(selectedOrder.trip).status === 'ONLINE' ? 'text-success' : 'text-danger'">
                    {{ getDriverStatus(selectedOrder.trip).status }}
                  </span>
                  <span class="text-muted" style="font-size: 12px;">({{ getDriverStatus(selectedOrder.trip).text }})</span>
                </div>
                <span class="text-muted">|</span>
                <span class="text-muted" style="font-size: 12px;">
                  Update Terakhir: <strong class="text-info">{{ getDriverStatus(selectedOrder.trip).timeText }}</strong>
                </span>
              </div>
            </div>

            <!-- Milestones Timestamps Panel -->
            <div class="row g-3 mb-3" style="font-size: 13px;">
              <div class="col-6 col-md-3">
                <div class="p-2.5 rounded bg-dark-custom border border-secondary-custom text-center h-100">
                  <span class="text-muted d-block small mb-1">ASSIGNED DATE</span>
                  <strong class="text-gray-900">{{ selectedOrder?.assigned_at ? formatDateTime(selectedOrder.assigned_at) : '-' }}</strong>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="p-2.5 rounded bg-dark-custom border border-secondary-custom text-center h-100">
                  <span class="text-muted d-block small mb-1">TASK ACCEPT DATE</span>
                  <strong class="text-gray-900">{{ selectedOrder?.accepted_at ? formatDateTime(selectedOrder.accepted_at) : '-' }}</strong>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="p-2.5 rounded bg-dark-custom border border-secondary-custom text-center h-100">
                  <span class="text-muted d-block small mb-1">ARRIVED DATE</span>
                  <strong class="text-gray-900">{{ selectedOrder?.arrived_at ? formatDateTime(selectedOrder.arrived_at) : '-' }}</strong>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="p-2.5 rounded bg-dark-custom border border-secondary-custom text-center h-100">
                  <span class="text-muted d-block small mb-1">HANDOVER DATE (HO)</span>
                  <strong class="text-gray-900">{{ selectedOrder?.pod_received_at ? formatDateTime(selectedOrder.pod_received_at) : '-' }}</strong>
                </div>
              </div>
            </div>

            <div class="row g-4">
            <!-- Left: Map Container -->
            <div class="col-12 col-lg-7">
              <div id="map-container" class="position-relative border border-secondary-custom rounded-3 overflow-hidden bg-light" style="height: 450px;">
                <div id="map" class="w-100 h-100" style="position: absolute; top:0; bottom:0; left:0; right:0;"></div>
              </div>
              <div class="mt-2 text-muted small d-flex align-items-center gap-3 justify-content-center flex-wrap">
                <span><span class="legend-dot bg-primary"></span> Rencana Rute</span>
                <span><span class="legend-dot bg-success"></span> Lintasan GPS Supir</span>
                <span><span class="legend-dot bg-warning"></span> Posisi Truk Live</span>
              </div>
            </div>

            <!-- Right: Vertical Timeline -->
            <div class="col-12 col-lg-5 d-flex flex-column">
              <h6 class="fw-bold text-gray-900 mb-3 border-bottom border-secondary-custom pb-2">Lini Masa Perjalanan</h6>
              <div class="timeline flex-grow-1 overflow-auto pe-2" style="max-height: 400px;">
                <div 
                  v-for="(log, idx) in selectedOrder?.status_logs" 
                  :key="log.id" 
                  class="timeline-item"
                  :class="{ 'first-item': idx === 0 }"
                >
                  <!-- Icon/Marker -->
                  <div class="timeline-marker" :class="getStatusMarkerClass(log.status)">
                    <i class="bi" :class="getStatusIconClass(log.status)"></i>
                  </div>
                  
                  <!-- Content -->
                  <div class="timeline-content">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <h6 class="fw-bold text-gray-900 mb-0" style="font-size: 14px;">{{ log.status }}</h6>
                      <span class="text-muted small">{{ formatDateTime(log.created_at) }}</span>
                    </div>
                    <p class="text-muted small mb-1">{{ log.description }}</p>
                    <span class="text-info small fs-7" v-if="log.changer">
                      <i class="bi bi-person me-1"></i>Oleh: {{ log.changer.name }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>

          <div class="d-flex justify-content-end border-top border-secondary-custom mt-4 pt-3">
            <button type="button" class="btn btn-secondary" @click="closeModal">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import DataTable from '../components/DataTable.vue';

const toast = useToast();

const loading = ref(false);
const orders = ref([]);

const showModal = ref(false);
const modalLoading = ref(false);
const selectedOrder = ref(null);
const currentAddress = ref('');
const addressDetails = ref(null);

const mapInstance = ref(null);
const mapMarkers = ref([]);

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'order_number', header: 'Order Number' },
  { accessorKey: 'job_number', header: 'Job Number' },
  { accessorKey: 'trip_number', header: 'Trip Number' },
  { accessorKey: 'driver', header: 'Driver' },
  { accessorKey: 'customer.customer_name', header: 'Customer' },
  { accessorKey: 'origin_city', header: 'Origin' },
  { accessorKey: 'destination_city', header: 'Destination' },
  { accessorKey: 'status', header: 'Status' },
  { accessorKey: 'actions', header: 'Lacak' }
];

// Offline coordinate mappings for major Indonesian cities
const cityCoordinates = {
  'jakarta': [-6.2088, 106.8456],
  'tangerang': [-6.1783, 106.6319],
  'bekasi': [-6.2383, 106.9756],
  'depok': [-6.4025, 106.7942],
  'bogor': [-6.5971, 106.8060],
  'karawang': [-6.3073, 107.2913],
  'cikampek': [-6.4079, 107.4583],
  'purwakarta': [-6.5583, 107.4417],
  'subang': [-6.5714, 107.7583],
  'bandung': [-6.9175, 107.6191],
  'cirebon': [-6.7320, 108.5523],
  'semarang': [-6.9667, 110.4167],
  'yogyakarta': [-7.7956, 110.3695],
  'solo': [-7.5754, 110.8243],
  'surabaya': [-7.2575, 112.7521],
  'malang': [-7.9666, 112.6326],
};

const getCityCoord = (cityName) => {
  if (!cityName) return null;
  const name = cityName.toLowerCase().trim();
  for (const key in cityCoordinates) {
    if (name.includes(key)) {
      return cityCoordinates[key];
    }
  }
  return null;
};

// Dynamically load Leaflet resources via CDN
const loadLeaflet = () => {
  return new Promise((resolve) => {
    if (window.L) {
      resolve(window.L);
      return;
    }

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => {
      resolve(window.L);
    };
    document.head.appendChild(script);
  });
};

onMounted(() => {
  fetchOrders();
});

const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/shipment-orders');
    if (response.data.success) {
      orders.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data shipment order.');
  } finally {
    loading.value = false;
  }
};

const trackOrder = async (orderRow) => {
  selectedOrder.value = orderRow;
  showModal.value = true;
  modalLoading.value = true;

  try {
    const response = await axios.get(`/shipment-orders/${orderRow.id}`);
    if (response.data.success) {
      selectedOrder.value = response.data.data;
      nextTick(() => {
        initMap();
      });
    }
  } catch (err) {
    toast.error('Gagal mengambil rincian lokasi pelacakan.');
  } finally {
    modalLoading.value = false;
  }
};

const closeModal = () => {
  if (mapInstance.value) {
    mapInstance.value.remove();
    mapInstance.value = null;
  }
  showModal.value = false;
  selectedOrder.value = null;
  currentAddress.value = '';
  addressDetails.value = null;
};
const resolveCoord = async (cityName, fallback) => {
  if (!cityName) return fallback;
  const offline = getCityCoord(cityName);
  if (offline) return offline;

  try {
    const response = await axios.get('https://nominatim.openstreetmap.org/search', {
      params: {
        format: 'json',
        q: `${cityName}, Indonesia`,
        limit: 1
      }
    });
    if (response.data && response.data.length > 0) {
      return [parseFloat(response.data[0].lat), parseFloat(response.data[0].lon)];
    }
  } catch (e) {
    console.error('Gagal mengambil koordinat geocoding untuk kota:', cityName, e);
  }
  return fallback;
};

const initMap = async () => {
  if (!selectedOrder.value) return;

  const L = await loadLeaflet();
  if (!L) return;

  // Fix Leaflet default icon paths pointing to invalid local assets under bundlers like Vite
  delete L.Icon.Default.prototype._getIconUrl;
  L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  });

  await nextTick();
  const mapElement = document.getElementById('map');
  if (!mapElement) return;

  if (mapInstance.value) {
    mapInstance.value.remove();
    mapInstance.value = null;
  }

  const originCoord = await resolveCoord(selectedOrder.value.origin_city, [-6.2088, 106.8456]);
  const destCoord = await resolveCoord(selectedOrder.value.destination_city, [-6.9175, 107.6191]);

  const map = L.map('map').setView(originCoord, 10);
  mapInstance.value = map;

  L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
  }).addTo(map);

  mapMarkers.value = [];

  const trip = selectedOrder.value.trip;
  let liveCoord = null;
  let isFallbackToOrigin = false;

  if (trip && trip.current_latitude && trip.current_longitude) {
    liveCoord = [parseFloat(trip.current_latitude), parseFloat(trip.current_longitude)];
  } else if (trip) {
    liveCoord = originCoord;
    isFallbackToOrigin = true;
  }

  // Origin Marker
  const originMarker = L.marker(originCoord)
    .bindPopup(`<strong>Kota Asal:</strong> ${selectedOrder.value.origin_city}`)
    .addTo(map);
  mapMarkers.value.push(originMarker);

  // Destination Marker
  const destMarker = L.marker(destCoord)
    .bindPopup(`<strong>Kota Tujuan:</strong> ${selectedOrder.value.destination_city}<br><small class="text-muted">${selectedOrder.value.detail_address}</small>`)
    .addTo(map);
  mapMarkers.value.push(destMarker);

  // Planned Route line
  L.polyline([originCoord, destCoord], {
    color: '#0d6efd',
    weight: 2,
    dashArray: '5, 8',
    opacity: 0.6
  }).addTo(map);

  // GPS history path line
  if (trip && trip.location_logs && trip.location_logs.length > 0) {
    const logCoords = trip.location_logs.map(log => [parseFloat(log.latitude), parseFloat(log.longitude)]);
    if (logCoords.length > 1) {
      L.polyline(logCoords, {
        color: '#198754',
        weight: 4,
        opacity: 0.8
      }).addTo(map);
    }
  }

  if (liveCoord) {
    const truckIcon = L.icon({
      iconUrl: 'https://cdn-icons-png.flaticon.com/512/744/744465.png',
      iconSize: [36, 36],
      iconAnchor: [18, 18],
      popupAnchor: [0, -18]
    });

    const truckMarker = L.marker(liveCoord, { icon: truckIcon })
      .bindPopup(`
        <strong style="color:#111827">Live Tracking Armada</strong><br>
        <span style="color:#4b5563">Supir: ${trip.driver?.driver_name || '-'}</span><br>
        <span style="color:#4b5563">Plat: ${trip.vehicle?.vehicle_no || '-'}</span>
        <div id="popup-address-text" style="color:#4b5563; font-size:11px; margin-top:4px; max-width:250px;">
          ${isFallbackToOrigin ? '<span style="color:#b45309; font-weight:bold;">⚠️ Belum ada GPS log (posisi di origin)</span>' : 'Mengambil alamat...'}
        </div>
      `)
      .addTo(map);
    mapMarkers.value.push(truckMarker);

    if (isFallbackToOrigin) {
      currentAddress.value = `Armada berada di lokasi Origin (${selectedOrder.value.origin_city})`;
    } else {
      currentAddress.value = `Mendeteksi koordinat GPS (${liveCoord[0]}, ${liveCoord[1]}). Mengambil alamat...`;
      axios.get(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${liveCoord[0]}&lon=${liveCoord[1]}`, {
        headers: { 'Accept-Language': 'id' }
      })
      .then(res => {
        const address = res.data.display_name || 'Alamat tidak terdeteksi';
        currentAddress.value = address;
        
        const addr = res.data.address || {};
        addressDetails.value = {
          jalan: addr.road || addr.construction || addr.pedestrian || null,
          kelurahan: addr.suburb || addr.village || addr.neighbourhood || addr.hamlet || null,
          kecamatan: addr.city_district || addr.subdistrict || addr.municipality || null,
          kota: addr.city || addr.regency || addr.county || null,
          provinsi: addr.state || null,
          kodepos: addr.postcode || null
        };
        
        // Update popup content dynamically if the marker exists
        truckMarker.setPopupContent(`
          <strong style="color:#111827">Live Tracking Armada</strong><br>
          <span style="color:#4b5563">Supir: ${trip.driver?.driver_name || '-'}</span><br>
          <span style="color:#4b5563">Plat: ${trip.vehicle?.vehicle_no || '-'}</span>
          <div style="color:#4b5563; font-size:11px; margin-top:4px; max-width:250px; word-wrap:break-word;">
            <strong>Lokasi Saat Ini:</strong><br>${address}
          </div>
        `);
      })
      .catch(() => {
        const fallbackTxt = `Koordinat GPS: ${liveCoord[0]}, ${liveCoord[1]}`;
        currentAddress.value = fallbackTxt;
        truckMarker.setPopupContent(`
          <strong style="color:#111827">Live Tracking Armada</strong><br>
          <span style="color:#4b5563">Supir: ${trip.driver?.driver_name || '-'}</span><br>
          <span style="color:#4b5563">Plat: ${trip.vehicle?.vehicle_no || '-'}</span>
          <div style="color:#ef4444; font-size:11px; margin-top:4px;">
            <strong>Lokasi Saat Ini:</strong><br>${fallbackTxt} (Gagal mengambil nama alamat)
          </div>
        `);
      });
    }
  }

  // Fit bounds dynamically after a short delay to allow container dimensions to settle in the DOM
  setTimeout(() => {
    map.invalidateSize();
    if (mapMarkers.value.length > 0) {
      const group = L.featureGroup(mapMarkers.value);
      map.fitBounds(group.getBounds().pad(0.2));
    }
  }, 250);
};

const getDriverStatus = (trip) => {
  if (!trip || !trip.location_logs || trip.location_logs.length === 0) {
    return {
      status: 'OFFLINE',
      text: 'GPS Tidak Aktif',
      badgeClass: 'bg-secondary',
      timeText: '-'
    };
  }

  const sortedLogs = [...trip.location_logs].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  const latestLog = sortedLogs[0];
  
  const lastTime = new Date(latestLog.created_at);
  const now = new Date();
  const diffMs = now - lastTime;
  const diffMins = Math.floor(diffMs / 60000);

  const isOnline = diffMins >= 0 && diffMins <= 15;

  return {
    status: isOnline ? 'ONLINE' : 'OFFLINE',
    text: isOnline ? 'Aktif mengirim lokasi' : 'Sinyal Hilang / GPS Nonaktif',
    badgeClass: isOnline ? 'bg-success' : 'bg-danger',
    timeText: formatDateTime(latestLog.created_at)
  };
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-';
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'DRAFT': return 'bg-secondary';
    case 'ASSIGNED': return 'bg-primary';
    case 'IN_TRANSIT': return 'bg-warning text-dark';
    case 'DELIVERED': return 'bg-success';
    case 'CANCELLED': return 'bg-danger';
    default: return 'bg-light text-dark';
  }
};

const getStatusMarkerClass = (status) => {
  switch (status) {
    case 'DRAFT': return 'bg-secondary border-secondary';
    case 'ASSIGNED': return 'bg-primary border-primary';
    case 'IN_TRANSIT': return 'bg-warning border-warning text-dark';
    case 'DELIVERED': return 'bg-success border-success';
    case 'CANCELLED': return 'bg-danger border-danger';
    default: return 'bg-light border-light text-dark';
  }
};

const getStatusIconClass = (status) => {
  switch (status) {
    case 'DRAFT': return 'bi-file-earmark-plus';
    case 'ASSIGNED': return 'bi-link-45deg';
    case 'IN_TRANSIT': return 'bi-truck';
    case 'DELIVERED': return 'bi-check-circle-fill';
    case 'CANCELLED': return 'bi-x-circle-fill';
    default: return 'bi-circle';
  }
};
</script>

<style scoped>
.fs-7 {
  font-size: 0.785rem;
}

/* Legend styling */
.legend-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 4px;
}

/* Modal styles */
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(4px);
  z-index: 1050;
}

.modal-dialog-custom {
  width: 96%;
  max-width: 1450px;
  margin: 1.5rem auto;
  max-height: 92vh;
  overflow-y: auto;
}

/* Timeline Styles */
.timeline {
  position: relative;
  padding-left: 30px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 10px;
  top: 15px;
  bottom: 15px;
  width: 2px;
  background-color: rgba(0, 0, 0, 0.08);
}

.timeline-item {
  position: relative;
  margin-bottom: 25px;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -30px;
  top: 0;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  color: #fff;
  z-index: 2;
  border: 2px solid #ffffff;
}

.timeline-item.first-item .timeline-marker {
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
  animation: pulse-glow 2s infinite;
}

.timeline-content {
  background-color: #f8f9fa;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 8px;
  padding: 12px 15px;
}

@keyframes pulse-glow {
  0% {
    box-shadow: 0 0 0 0px rgba(13, 110, 253, 0.4);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(13, 110, 253, 0);
  }
  100% {
    box-shadow: 0 0 0 0px rgba(13, 110, 253, 0);
  }
}
</style>
