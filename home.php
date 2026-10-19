<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$stmt = $pdo->prepare('SELECT latitude, longitude FROM user_locations WHERE user_id = ?');
$stmt->execute([$userId]);
$userLoc = $stmt->fetch();

$stmt = $pdo->prepare('SELECT id, name FROM `groups` WHERE owner_id = ?');
$stmt->execute([$userId]);
$adminGroups = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Révolution | Ana Sayfa</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Orbitron:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <style>
        .modal-overlay.active { background: rgba(0, 0, 0, 0.4) !important; backdrop-filter: none !important; pointer-events: none; justify-content: flex-start; padding-left: 300px; }
        .modal-box { pointer-events: auto; box-shadow: 0 0 50px rgba(0,0,0,0.8); }
        #locationPicker { cursor: crosshair; transition: all 0.3s ease; }
        .map-instruction { position: absolute; top: 20px; left: 50%; transform: translateX(-50%); background: #ff1238; color: white; padding: 12px 25px; border-radius: 50px; font-weight: bold; font-size: 14px; z-index: 1000; box-shadow: 0 5px 15px rgba(0,0,0,0.6); display: none; animation: pulse 1.5s infinite; white-space: nowrap; }
        @keyframes pulse { 0% { transform: translateX(-50%) scale(1); } 50% { transform: translateX(-50%) scale(1.05); } 100% { transform: translateX(-50%) scale(1); } }
        .input-error { border: 2px solid #ff1238 !important; background-color: #ff123810 !important; }

        .user-location-pin {
            background-color: var(--red);
            width: 24px; height: 24px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            border: 3px solid #fff;
            box-shadow: 0 0 15px rgba(255, 18, 56, 0.8);
            display: flex; justify-content: center; align-items: center;
        }
        .user-location-pin::after {
            content: '';
            width: 10px; height: 10px;
            background: #fff;
            border-radius: 50%;
            position: absolute;
        }

        .locate-btn {
            position: absolute; right: 20px; bottom: 30px; z-index: 1000;
            width: 50px; height: 50px; border-radius: 50%;
            background: #0d0b0b; border: 2px solid var(--yellow);
            color: var(--yellow); font-size: 20px; cursor: pointer;
            display: flex; justify-content: center; align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.6);
            transition: all 0.3s;
        }
        .locate-btn:hover { background: var(--yellow); color: #000; transform: scale(1.1); }
    </style>
</head>
<body class="home-body">

<header class="topbar">
    <div class="topbar-left"><i class="fa-solid fa-crown" style="color:var(--yellow);"></i><span class="topbar-brand">RÉVOLUTION</span></div>
    <div class="topbar-right"><span class="topbar-user"><i class="fa-regular fa-user"></i> <?= htmlspecialchars($userName) ?></span><a href="logout.php" class="topbar-logout"><i class="fa-solid fa-right-from-bracket"></i></a></div>
</header>

<div class="home-layout">
    <aside class="sidebar">
        <nav class="side-nav">
            <a href="home.php" class="side-link active"><i class="fa-solid fa-map-location-dot"></i><span>Harita</span></a>
            <a href="my_events.php" class="side-link"><i class="fa-solid fa-calendar-days"></i><span>Etkinliklerim</span></a>
            <a href="groups.php" class="side-link"><i class="fa-solid fa-users"></i><span>Gruplarım</span></a>
            <a href="profile.php" class="side-link"><i class="fa-regular fa-circle-user"></i><span>Profil</span></a>
        </nav>
        
        <div class="side-section">
            <h4 class="side-title"><i class="fa-solid fa-fire" style="color:var(--yellow);"></i> Çevredeki Etkinlikler</h4>
            <div id="nearbyEventsList" class="nearby-list"><p class="loading-text">Konum alınıyor...</p></div>
        </div>

        <button class="create-event-btn" id="openCreateEventModal"><i class="fa-solid fa-plus"></i> Etkinlik Oluştur</button>
    </aside>

    <main class="map-area">
        <div id="map" class="map-container"></div>
        <div id="mapInstruction" class="map-instruction"><i class="fa-solid fa-location-dot"></i> Haritadan etkinlik yerini seçmek için tıklayın</div>
        
        <button class="locate-btn" id="locateMeBtn" title="Konumumu Ortala"><i class="fa-solid fa-crosshairs"></i></button>
        
        <div id="mapStatus" class="map-status"><i class="fa-solid fa-location-dot"></i><span>Konumunuz alınıyor...</span></div>
    </main>
</div>

<div class="modal-overlay" id="eventModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-calendar-plus" style="color:var(--yellow);"></i> Yeni Etkinlik</h3>
            <button class="modal-close" id="closeEventModal">&times;</button>
        </div>
        <form action="event_create_process.php" method="post" id="createEventForm">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="latitude" id="eventLat" required>
            <input type="hidden" name="longitude" id="eventLng" required>
            <input type="text" id="locationValidationInput" required style="opacity: 0; position: absolute; pointer-events: none; height: 0; width: 0;" tabindex="-1">

            <div class="modal-field">
                <label>Etkinlik Adı *</label>
                <input type="text" name="title" placeholder="Örn: Mahalle Toplantısı" required maxlength="150">
            </div>
            
            <div class="modal-field">
                <label>Gizlilik / Kapsam</label>
                <select name="group_id" id="groupSelect" style="width: 100%; padding: 11px; border-radius: 10px; background: #ffffff06; border: 1px solid #ffffff20; color: var(--white);">
                    <option value="">🌍 Herkese Açık Etkinlik</option>
                    <?php if(count($adminGroups) > 0): ?>
                        <optgroup label="Yöneticisi Olduğun Gruplar (Gizli)">
                            <?php foreach($adminGroups as $ag): ?>
                                <option value="<?= $ag['id'] ?>">🔒 Sadece <?= htmlspecialchars($ag['name']) ?> Üyeleri</option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>

            <div class="modal-field">
                <label>Konu / Tür</label>
                <select name="event_type">
                    <option value="toplanti">Toplantı</option>
                    <option value="spor">Spor</option>
                    <option value="parti">Parti</option>
                    <option value="studyo">Çalışma Grubu</option>
                    <option value="diger">Diğer</option>
                </select>
            </div>

            <div class="modal-field">
                <label>Açıklama</label>
                <textarea name="description" rows="3" placeholder="Kısa bir açıklama..."></textarea>
            </div>
            
            <div class="modal-field">
                <label>Tarih & Saat *</label>
                <input type="datetime-local" name="event_date" min="<?= date('Y-m-d\TH:i') ?>" required>
            </div>
            
            <div class="modal-field">
                <label>Konum Seçimi *</label>
                <div class="location-picker" id="locationPicker">
                    <i class="fa-solid fa-location-crosshairs" style="color:var(--yellow);"></i>
                    <span id="selectedLocationText" style="color:#ccc;">Haritanın üzerine tıklayarak yer seçin</span>
                </div>
                <button type="button" class="use-my-location-btn" id="useMyLocationBtn"><i class="fa-solid fa-crosshairs"></i> Kendi Konumumu Kullan</button>
            </div>
            
            <button type="submit" class="modal-submit"><i class="fa-solid fa-check"></i> Etkinliği Oluştur</button>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map, userMarker, eventMarkers = [];
let selectedLat = null, selectedLng = null;
let userLat = null, userLng = null;
let selectedMarker = null;

const savedLat = <?= $userLoc ? json_encode((float)$userLoc['latitude']) : 'null' ?>;
const savedLng = <?= $userLoc ? json_encode((float)$userLoc['longitude']) : 'null' ?>;
const defaultLat = 41.0082;
const defaultLng = 28.9784;

map = L.map('map').setView([defaultLat, defaultLng], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

if (savedLat && savedLng) setUserLocation(savedLat, savedLng, false);
else getUserLocation();

document.getElementById('locateMeBtn').addEventListener('click', function() {
    if (userLat && userLng) {
        map.flyTo([userLat, userLng], 16, { duration: 1.5 });
        if (userMarker) userMarker.openPopup();
    } else {
        alert("Konumunuz henüz alınamadı. Lütfen bekleyin veya tarayıcı konum izinlerini kontrol edin.");
        getUserLocation();
    }
});

map.on('click', function(e) {
    if (document.getElementById('eventModal').classList.contains('active')) {
        selectedLat = e.latlng.lat; selectedLng = e.latlng.lng;
        document.getElementById('eventLat').value = selectedLat;
        document.getElementById('eventLng').value = selectedLng;
        document.getElementById('locationValidationInput').value = "secildi";
        document.getElementById('locationPicker').classList.remove('input-error');
        document.getElementById('selectedLocationText').textContent = 'Seçildi: ' + selectedLat.toFixed(5) + ', ' + selectedLng.toFixed(5);
        document.getElementById('selectedLocationText').style.color = '#22c55e';
        if (selectedMarker) map.removeLayer(selectedMarker);
        selectedMarker = L.marker([selectedLat, selectedLng], {icon: L.divIcon({ className: 'custom-div-icon', html: `<div style="background-color:#ff1238; width:20px; height:20px; border-radius:50%; border:3px solid #fff; box-shadow: 0 0 15px #ff1238;"></div>`, iconSize: [26, 26], iconAnchor: [13, 13] })}).addTo(map);
    }
});

function getUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => { userLat = position.coords.latitude; userLng = position.coords.longitude; setUserLocation(userLat, userLng, true); },
            () => { document.getElementById('mapStatus').innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i><span>Konum alınamadı.</span>'; loadNearbyEvents(defaultLat, defaultLng); },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }
}

function setUserLocation(lat, lng, saveToDb) {
    userLat = lat; userLng = lng; map.setView([lat, lng], 15);
    if (userMarker) map.removeLayer(userMarker);
    
    const userPinIcon = L.divIcon({
        className: 'custom-user-pin',
        html: `<div class="user-location-pin"></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 24]
    });

    userMarker = L.marker([lat, lng], {icon: userPinIcon}).addTo(map).bindPopup('<b>Buradasınız</b>');
    document.getElementById('mapStatus').innerHTML = '<i class="fa-solid fa-location-dot" style="color:#22c55e;"></i><span>Konumunuz bulundu</span>';
    
    if (saveToDb) fetch('save_location.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ lat, lng, csrf: '<?= csrf_token() ?>' }) }).catch(() => {});
    loadNearbyEvents(lat, lng);
}

function loadNearbyEvents(lat, lng) {
    fetch(`get_nearby_events.php?lat=${lat}&lng=${lng}&radius=10`)
        .then(r => r.json())
        .then(events => {
            eventMarkers.forEach(m => map.removeLayer(m)); eventMarkers = [];
            const list = document.getElementById('nearbyEventsList');
            if (events.length === 0) { list.innerHTML = '<p class="empty-text">Çevrede henüz bir etkinlik yok.</p>'; return; }
            list.innerHTML = '';
            events.forEach(ev => {
                const isJoined = ev.is_joined > 0;
                const isMine = ev.user_id == <?= $userId ?>;
                
                // RENK MANTIĞI (MOR ÖNCELİKLİ)
                let pinColor = '#ffd500'; // Sarı
                if (ev.group_id) {
                    pinColor = '#9b59b6'; // Grup ise Mor
                } else if (isMine) {
                    pinColor = '#00d2ff'; // Benim ise Mavi
                } else if (isJoined) {
                    pinColor = '#22c55e'; // Katıldıysam Yeşil
                }

                const marker = L.marker([parseFloat(ev.latitude), parseFloat(ev.longitude)], {icon: L.divIcon({ className: 'custom-div-icon', html: `<div style="background-color:${pinColor}; width:16px; height:16px; border-radius:50%; border:2px solid #111; box-shadow: 0 0 10px ${pinColor};"></div>`, iconSize: [20, 20], iconAnchor: [10, 10] })}).addTo(map);
                
                let joinButtonHTML = isJoined 
                    ? `<a href="event_join.php?id=${ev.id}&action=leave" style="display:inline-block; margin-top:8px; background:#ff123820; color:#ff1238; padding:6px 12px; border-radius:5px; text-decoration:none; font-size:11px; font-weight:bold;">Ayrıl</a>`
                    : `<a href="event_join.php?id=${ev.id}&action=join" style="display:inline-block; margin-top:8px; background:#22c55e20; color:#22c55e; padding:6px 12px; border-radius:5px; text-decoration:none; font-size:11px; font-weight:bold;">Katıl</a>`;

                let scopeHTML = ev.group_id ? `<span style="color:#9b59b6; font-weight:bold;"><i class="fa-solid fa-users"></i> ${ev.group_name} Grubu</span>` : `<span style="color:#ff8c00; font-weight:bold;"><i class="fa-solid fa-globe"></i> Herkese Açık</span>`;

                const dateStr = new Date(ev.event_date).toLocaleDateString('tr-TR');
                const timeStr = new Date(ev.event_date).toLocaleTimeString('tr-TR', {hour: '2-digit', minute:'2-digit'});

                const popupHTML = `
                    <div style="text-align:center; min-width:170px;">
                        <b style="font-size:14px; color:#111;">${ev.title}</b><br>
                        <div style="font-size:11px; color:#555; margin-top:4px; margin-bottom:4px;">
                            ${scopeHTML}<br>
                            <i class="fa-solid fa-user-pen"></i> Düzenleyen: <b>${ev.creator_name}</b>
                        </div>
                        <span style="font-size:11px; color:#333;">Tür: ${ev.event_type}</span><br>
                        <span style="font-size:11px; color:#333; font-weight:bold;"><i class="fa-regular fa-clock"></i> ${dateStr} - ${timeStr}</span><br>
                        <span style="font-size:11px; color:#333; font-weight:bold; margin-top:2px; display:block;">${ev.participant_count} katılımcı</span>
                        ${joinButtonHTML}
                    </div>
                `;
                marker.bindPopup(popupHTML);
                eventMarkers.push(marker);

                const item = document.createElement('div');
                item.className = 'nearby-item';
                item.innerHTML = `<i class="fa-solid fa-calendar-check" style="color:${pinColor} !important; text-shadow: 0 0 5px ${pinColor};"></i><div><strong>${ev.title}</strong><small><i class="fa-regular fa-clock"></i> ${dateStr} - ${timeStr}</small></div>`;
                item.addEventListener('click', () => { map.setView([parseFloat(ev.latitude), parseFloat(ev.longitude)], 17); marker.openPopup(); });
                list.appendChild(item);
            });
        }).catch(() => {});
}

// MODAL KONTROLLERİ VE TEMİZLİK
document.getElementById('openCreateEventModal').addEventListener('click', () => { 
    document.getElementById('eventModal').classList.add('active'); 
    document.getElementById('mapInstruction').style.display = 'block'; 
});

document.getElementById('closeEventModal').addEventListener('click', () => { 
    document.getElementById('eventModal').classList.remove('active'); 
    document.getElementById('mapInstruction').style.display = 'none'; 
    
    if (selectedMarker) {
        map.removeLayer(selectedMarker);
        selectedMarker = null;
    }
    
    document.getElementById('eventLat').value = "";
    document.getElementById('eventLng').value = "";
    document.getElementById('locationValidationInput').value = "";
    
    document.getElementById('selectedLocationText').textContent = "Haritanın üzerine tıklayarak yer seçin";
    document.getElementById('selectedLocationText').style.color = "#ccc";
    document.getElementById('locationPicker').classList.remove('input-error');
});

document.getElementById('useMyLocationBtn').addEventListener('click', () => {
    if (userLat && userLng) {
        selectedLat = userLat; selectedLng = userLng;
        document.getElementById('eventLat').value = userLat; 
        document.getElementById('eventLng').value = userLng;
        document.getElementById('locationValidationInput').value = "secildi";
        document.getElementById('locationPicker').classList.remove('input-error');
        document.getElementById('selectedLocationText').textContent = 'Kendi Konumunuz Seçildi';
        document.getElementById('selectedLocationText').style.color = '#22c55e';
        map.setView([userLat, userLng], 17);
        if (selectedMarker) map.removeLayer(selectedMarker);
        selectedMarker = L.marker([userLat, userLng], {icon: L.divIcon({ className: 'custom-div-icon', html: `<div style="background-color:#ff1238; width:20px; height:20px; border-radius:50%; border:3px solid #fff; box-shadow: 0 0 15px #ff1238;"></div>`, iconSize: [26, 26], iconAnchor: [13, 13] })}).addTo(map);
    }
});

// TARAYICI UYARILI FORM KONTROLÜ
document.getElementById('createEventForm').addEventListener('submit', function(e) {
    if (!document.getElementById('eventLat').value) {
        e.preventDefault(); 
        document.getElementById('locationPicker').classList.add('input-error');
        document.getElementById('locationValidationInput').value = ""; 
        document.getElementById('locationValidationInput').reportValidity();
        return false;
    }
});
</script>
</body>
</html>