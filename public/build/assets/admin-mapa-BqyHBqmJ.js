let o,c=[],w=[],u;window.initMap=function(){const a=document.getElementById("map");if(!a)return;const s=window.MAPA_DATA||{},t=s.markers||[],l=s.mapId;u=new google.maps.LatLngBounds;const r={lat:-8.8383,lng:13.2344};try{if(!google.maps.marker||!google.maps.marker.AdvancedMarkerElement){console.warn("⚠️ AdvancedMarkerElement indisponível, usando legacy."),C(t,r);return}o=new google.maps.Map(a,{center:r,zoom:5,mapId:l,mapTypeId:"hybrid",tilt:45,heading:0,zoomControl:!0,mapTypeControl:!0,mapTypeControlOptions:{style:google.maps.MapTypeControlStyle.HORIZONTAL_BAR,position:google.maps.ControlPosition.TOP_RIGHT,mapTypeIds:["roadmap","satellite","hybrid","terrain"]},scaleControl:!0,streetViewControl:!0,streetViewControlOptions:{position:google.maps.ControlPosition.RIGHT_TOP},fullscreenControl:!0,fullscreenControlOptions:{position:google.maps.ControlPosition.BOTTOM_RIGHT}}),o.addListener("click",function(){w.forEach(n=>n.close()),window.highlightInfoWindow&&window.highlightInfoWindow.close()}),t.length>0?(t.forEach(n=>{const g=document.createElement("div"),b=n.nome?n.nome.charAt(0).toUpperCase():"E";g.innerHTML=`
                    <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; cursor: pointer; transition: transform 0.2s ease;">
                        <div style="position: absolute; width: 44px; height: 44px; background: #1a56db; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 12px rgba(26, 86, 219, 0.4); display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-weight: bold; font-size: 16px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                ${b}
                            </span>
                        </div>
                        <div style="position: absolute; bottom: -8px; width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 12px solid #1a56db; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></div>
                    </div>
                `;const h=new google.maps.marker.AdvancedMarkerElement({map:o,position:{lat:parseFloat(n.lat),lng:parseFloat(n.lng)},title:n.nome,content:g.firstElementChild}),y=document.createElement("div"),x=n.foto?`<img src="${n.foto}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #1a56db;">`:`<div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #1a56db, #3b82f6); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 1.1rem;">${b}</div>`;y.innerHTML=`
                    <div style="min-width: 220px; max-width: 280px; padding: 4px 0;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            ${x}
                            <div>
                                <strong style="color: #1a56db; font-size: 0.9rem; display: block;">${n.nome}</strong>
                                <small style="color: #64748b; font-size: 0.72rem;">${n.numero}</small>
                            </div>
                        </div>
                        <hr style="margin: 6px 0; border-color: #e2e8f0;">
                        <div style="font-size: 0.82rem;">
                            <p style="margin: 4px 0; color: #334155;">
                                <i class="fas fa-map-marker-alt" style="color: #1a56db;"></i>
                                <strong>${n.cidade?n.cidade+", ":""}${n.pais}</strong>
                            </p>
                            <p style="margin: 4px 0; color: #64748b; font-size: 0.72rem; font-family: monospace;">
                                ${parseFloat(n.lat).toFixed(4)}, ${parseFloat(n.lng).toFixed(4)}
                            </p>
                        </div>
                        <hr style="margin: 8px 0; border-color: #e2e8f0;">
                        <a href="/admin/egressos/${n.id}" style="display: block; text-align: center; padding: 8px 12px; background: linear-gradient(135deg, #1a56db, #3b82f6); color: #fff; text-decoration: none; border-radius: 8px; font-size: 0.8rem; font-weight: 600;">
                            <i class="fas fa-eye"></i> Ver Perfil
                        </a>
                    </div>
                `;const I=new google.maps.InfoWindow({content:y.firstElementChild,maxWidth:300});h.addListener("click",function(){w.forEach(E=>E.close()),I.open(o,h)}),u.extend(new google.maps.LatLng(parseFloat(n.lat),parseFloat(n.lng))),c.push(h),w.push(I)}),c.length>0&&(o.fitBounds(u),c.length===1&&o.setZoom(14))):(o.setCenter(r),o.setZoom(5))}catch(n){console.error("Erro ao carregar o mapa moderno:",n),C(t,r)}};function C(a,s){try{o=new google.maps.Map(document.getElementById("map"),{center:s,zoom:5,mapTypeId:"hybrid",zoomControl:!0,mapTypeControl:!0,mapTypeControlOptions:{position:google.maps.ControlPosition.TOP_RIGHT},scaleControl:!0,streetViewControl:!0,streetViewControlOptions:{position:google.maps.ControlPosition.RIGHT_TOP},fullscreenControl:!0}),o.addListener("click",function(){w.forEach(t=>t.close()),window.highlightInfoWindow&&window.highlightInfoWindow.close()}),a.forEach(t=>{const l=new google.maps.Marker({position:{lat:parseFloat(t.lat),lng:parseFloat(t.lng)},map:o,title:t.nome,animation:google.maps.Animation.DROP}),r=`
                <div style="min-width: 220px; max-width: 280px; padding: 10px;">
                    <strong style="color: #1a56db;">${t.nome}</strong><br>
                    <small style="color: #64748b;">${t.numero}</small><br>
                    <p style="margin: 6px 0;">📍 <strong>${t.cidade?t.cidade+", ":""}${t.pais}</strong></p>
                    <a href="/admin/egressos/${t.id}" style="display: block; text-align: center; padding: 8px 12px; background: linear-gradient(135deg, #1a56db, #3b82f6); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.8rem;">
                       <i class="fas fa-eye"></i> Ver Perfil
                    </a>
                </div>
            `,n=new google.maps.InfoWindow({content:r,maxWidth:300});l.addListener("click",function(){w.forEach(g=>g.close()),n.open(o,l)}),u.extend(new google.maps.LatLng(parseFloat(t.lat),parseFloat(t.lng))),c.push(l),w.push(n)}),c.length>0&&(o.fitBounds(u),c.length===1&&o.setZoom(14))}catch(t){console.error("Erro no fallback:",t)}}document.addEventListener("DOMContentLoaded",()=>{const a=document.getElementById("searchEgresso"),s=document.getElementById("btnBuscar"),t=document.getElementById("btnLimpar"),l=document.getElementById("searchResult"),r=document.getElementById("searchError"),n=document.getElementById("searchErrorMessage"),g=document.getElementById("searchNome"),b=document.getElementById("searchLocalizacao"),h=document.getElementById("searchAvatar");if(!s)return;function y(){const i=a.value.trim();if(!i){v("Por favor, digite um número de processo.");return}s.innerHTML='<span class="spinner-border spinner-border-sm" role="status"></span> Buscando...',s.disabled=!0,fetch(`/admin/mapa/buscar-egresso?numero_processo=${encodeURIComponent(i)}`).then(e=>e.json()).then(e=>{if(s.innerHTML='<i class="fas fa-map-marker-alt me-1"></i><span class="d-none d-sm-inline">Localizar</span>',s.disabled=!1,!e.success){v(e.message);return}r.style.display="none",l.style.display="block",g.textContent=`${e.egresso.nome} (${e.egresso.numero_processo})`;const m=e.localizacao.cidade&&e.localizacao.cidade!=="Não informado"?`${e.localizacao.cidade}, ${e.localizacao.pais}`:e.localizacao.pais;if(b.textContent=`📍 ${m}`,e.egresso.foto)h.innerHTML=`<img src="${e.egresso.foto}" alt="Foto">`;else{const d=e.egresso.nome.split(" ").map(f=>f[0]).join("").substring(0,2).toUpperCase();h.innerHTML=`<div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #1a56db, #3b82f6); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 1.1rem;">${d}</div>`}const p={lat:e.localizacao.lat,lng:e.localizacao.lng};o?(o.setCenter(p),o.setZoom(17),I(p,e.egresso.nome,e.localizacao.cidade,e.localizacao.pais)):console.warn("Mapa ainda não carregou, mas os dados foram encontrados."),document.getElementById("map").scrollIntoView({behavior:"smooth",block:"center"})}).catch(e=>{s.innerHTML='<i class="fas fa-map-marker-alt me-1"></i><span class="d-none d-sm-inline">Localizar</span>',s.disabled=!1,v("Erro ao buscar egresso. Tente novamente."),console.error("Erro:",e)})}function x(){window.highlightMarkerInstance&&(typeof window.highlightMarkerInstance.setMap=="function"?window.highlightMarkerInstance.setMap(null):window.highlightMarkerInstance.map=null,window.highlightMarkerInstance=null),window.highlightInfoWindow&&(window.highlightInfoWindow.close(),window.highlightInfoWindow=null)}function I(i,e,m,p){x();try{const d=document.createElement("div");d.innerHTML=`
                <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; cursor: pointer; animation: pulse 1.5s infinite;">
                    <div style="position: absolute; width: 60px; height: 60px; background: #ef4444; border-radius: 50%; border: 4px solid white; box-shadow: 0 0 0 10px rgba(239, 68, 68, 0.3), 0 4px 20px rgba(239, 68, 68, 0.5); display: flex; align-items: center; justify-content: center;">
                        <span style="color: white; font-weight: bold; font-size: 20px; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                            ${e?e.charAt(0).toUpperCase():"E"}
                        </span>
                    </div>
                    <div style="position: absolute; bottom: -12px; width: 0; height: 0; border-left: 12px solid transparent; border-right: 12px solid transparent; border-top: 18px solid #ef4444; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></div>
                </div>
                <style>
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.15); }
                        100% { transform: scale(1); }
                    }
                </style>
            `,window.highlightMarkerInstance=new google.maps.marker.AdvancedMarkerElement({map:o,position:i,content:d.firstElementChild,title:e||"Egresso"});const f=document.createElement("div");f.innerHTML=`
                <div style="padding: 12px;">
                    <h5 style="color: #1a56db; margin-bottom: 6px; font-weight: bold;">🎯 ${e||"Egresso"}</h5>
                    <p style="margin: 0; font-size: 0.9rem; color: #334155;">📍 ${m||"Cidade não informada"}, ${p||"País não informado"}</p>
                    <p style="margin: 6px 0 0 0; font-size: 0.78rem; color: #64748b; font-family: monospace;">${i.lat.toFixed(4)}, ${i.lng.toFixed(4)}</p>
                </div>
            `,window.highlightInfoWindow=new google.maps.InfoWindow({content:f.firstElementChild,maxWidth:300}),window.highlightMarkerInstance.addListener("click",function(){window.highlightInfoWindow.open(o,window.highlightMarkerInstance)}),setTimeout(()=>{window.highlightInfoWindow&&window.highlightMarkerInstance&&window.highlightInfoWindow.open(o,window.highlightMarkerInstance)},500)}catch(d){console.error("Erro no marcador moderno, tentando fallback:",d),E(i,e,m,p)}}function E(i,e,m,p){const d={path:google.maps.SymbolPath.CIRCLE,fillColor:"#ef4444",fillOpacity:1,strokeColor:"#ffffff",strokeWeight:4,scale:16,anchor:new google.maps.Point(0,0)};window.highlightMarkerInstance=new google.maps.Marker({position:i,map:o,title:e||"Egresso",icon:d,animation:google.maps.Animation.BOUNCE,zIndex:999});const f=`
            <div style="padding: 12px;">
                <h5 style="color: #1a56db; margin-bottom: 6px; font-weight: bold;">🎯 ${e||"Egresso"}</h5>
                <p style="margin: 0; font-size: 0.9rem; color: #334155;">📍 ${m||"Cidade não informada"}, ${p||"País não informado"}</p>
                <p style="margin: 6px 0 0 0; font-size: 0.78rem; color: #64748b; font-family: monospace;">${i.lat.toFixed(4)}, ${i.lng.toFixed(4)}</p>
            </div>
        `;window.highlightInfoWindow=new google.maps.InfoWindow({content:f,maxWidth:300}),window.highlightMarkerInstance.addListener("click",function(){window.highlightInfoWindow.open(o,window.highlightMarkerInstance)}),setTimeout(()=>{window.highlightInfoWindow&&window.highlightMarkerInstance&&window.highlightInfoWindow.open(o,window.highlightMarkerInstance)},500)}function v(i){r.style.display="block",l.style.display="none",n.textContent=i}function M(){a.value="",l.style.display="none",r.style.display="none",x(),o&&(o.setCenter({lat:-8.8383,lng:13.2344}),c.length>0?(o.fitBounds(u),c.length===1&&o.setZoom(14)):o.setZoom(5)),a.focus()}s.addEventListener("click",y),a.addEventListener("keypress",function(i){i.key==="Enter"&&y()}),t.addEventListener("click",M),document.addEventListener("keydown",function(i){(i.ctrlKey||i.metaKey)&&i.key==="f"&&(i.preventDefault(),a.focus(),a.select())})});
