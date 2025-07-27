document.addEventListener("DOMContentLoaded",function(){f(),h(),L()});function f(){const t=new IntersectionObserver(n=>{n.forEach(o=>{o.isIntersecting&&o.target.classList.add("animate-in")})},{threshold:.1,rootMargin:"0px 0px -50px 0px"});document.querySelectorAll(".sppd-form-section").forEach(n=>{t.observe(n)}),y(),g(),b()}function y(){document.querySelectorAll(".sppd-form-input, .sppd-form-select, .sppd-form-textarea").forEach(n=>{n.addEventListener("focus",function(){this.parentElement.classList.add("input-focused"),this.style.boxShadow="0 0 20px rgba(102, 126, 234, 0.3)"}),n.addEventListener("blur",function(){this.parentElement.classList.remove("input-focused"),this.style.boxShadow=""})})}function g(){document.querySelectorAll(".sppd-form-group").forEach(n=>{const o=n.querySelector("input, select, textarea"),e=n.querySelector("label");o&&e&&(o.value&&e.classList.add("floating"),o.addEventListener("focus",()=>{e.classList.add("floating")}),o.addEventListener("blur",()=>{o.value||e.classList.remove("floating")}))})}function b(){const t=document.createElement("div");t.className="sppd-tooltip",t.style.cssText=`
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        max-width: 200px;
        text-align: center;
    `,document.body.appendChild(t),document.querySelectorAll(".sppd-text-info").forEach(n=>{n.addEventListener("mouseenter",function(o){const e=this.querySelector("span").textContent;t.textContent=e,t.style.opacity="1";const a=this.getBoundingClientRect();t.style.left=a.left+a.width/2-t.offsetWidth/2+"px",t.style.top=a.top-t.offsetHeight-8+"px"}),n.addEventListener("mouseleave",function(){t.style.opacity="0"})})}function h(){v(),E(),x()}function v(){const t=document.querySelectorAll("#biaya_transport, #biaya_penginapan, #uang_harian, #biaya_lainnya"),n=document.getElementById("total_display");t.forEach(e=>{e.addEventListener("input",function(){this.style.backgroundColor="rgba(59, 130, 246, 0.1)",setTimeout(()=>{this.style.backgroundColor="",o()},500)})});function o(){const e=I();n&&(n.style.transform="scale(1.1)",n.style.transition="transform 0.3s ease",setTimeout(()=>{n.textContent="Rp "+formatRupiah(e),n.style.transform="scale(1)"},150))}}function E(){const t=document.getElementById("sppd-form"),n=document.querySelectorAll(".sppd-progress-step");t.addEventListener("input",function(){o()});function o(){var c,d;let e=0;document.getElementById("nama").value&&e++,document.getElementById("tujuan").value&&document.getElementById("keperluan").value&&e++,(document.getElementById("biaya_transport").value||document.getElementById("biaya_penginapan").value)&&e++,((d=(c=document.querySelector('input[type="file"]'))==null?void 0:c.files)==null?void 0:d.length)>0&&e++,n.forEach((l,u)=>{u<e?(l.classList.add("active"),l.querySelector(".sppd-progress-circle").innerHTML='<i class="fas fa-check"></i>'):(l.classList.remove("active"),l.querySelector(".sppd-progress-circle").innerHTML=u+1)})}}function x(){const t=document.getElementById("tujuan");t&&p(t,["KPU Provinsi Jawa Barat","KPU Provinsi DKI Jakarta","KPU Pusat - Jakarta","KPU Kabupaten Bandung","KPU Kabupaten Garut","KPU Kabupaten Tasikmalaya","KPU Kabupaten Cianjur","KPU Kabupaten Sukabumi"]);const o=document.getElementById("keperluan");o&&p(o,["Koordinasi kegiatan pemilihan","Rapat koordinasi dengan KPU Provinsi","Bimbingan teknis (Bimtek)","Sosialisasi peraturan KPU","Monitoring dan evaluasi","Pelatihan teknis","Rapat pleno KPU"])}function p(t,n){const o=document.createElement("div");o.className="sppd-autocomplete",o.style.cssText=`
        position: relative;
        display: inline-block;
        width: 100%;
    `;const e=document.createElement("div");e.className="sppd-suggestions",e.style.cssText=`
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    `,t.parentNode.insertBefore(o,t),o.appendChild(t),o.appendChild(e),t.addEventListener("input",function(){const a=this.value.toLowerCase();if(e.innerHTML="",a.length>0){const r=n.filter(i=>i.toLowerCase().includes(a));r.length>0?(r.forEach(i=>{const s=document.createElement("div");s.style.cssText=`
                        padding: 8px 12px;
                        cursor: pointer;
                        border-bottom: 1px solid #f3f4f6;
                    `,s.textContent=i,s.addEventListener("click",function(){t.value=i,e.style.display="none"}),s.addEventListener("mouseenter",function(){this.style.backgroundColor="#f3f4f6"}),s.addEventListener("mouseleave",function(){this.style.backgroundColor=""}),e.appendChild(s)}),e.style.display="block"):e.style.display="none"}else e.style.display="none"}),document.addEventListener("click",function(a){o.contains(a.target)||(e.style.display="none")})}function L(){const t=()=>{const n=window.innerWidth<768;document.querySelectorAll(".sppd-form-section").forEach(e=>{n?(e.style.padding="1.5rem",e.style.margin="0 0.5rem 1.5rem"):(e.style.padding="",e.style.margin="")})};window.addEventListener("resize",t),t()}function I(){var a,r,i,s;const t=parseNumber(((a=document.getElementById("biaya_transport"))==null?void 0:a.value)||"0"),n=parseNumber(((r=document.getElementById("biaya_penginapan"))==null?void 0:r.value)||"0"),o=parseNumber(((i=document.getElementById("uang_harian"))==null?void 0:i.value)||"0"),e=parseNumber(((s=document.getElementById("biaya_lainnya"))==null?void 0:s.value)||"0");return t+n+o+e}const m=document.createElement("style");m.textContent=`
    .animate-in {
        animation: slideInFromBottom 0.6s ease-out forwards;
    }
    
    @keyframes slideInFromBottom {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .input-focused {
        transform: scale(1.02);
        transition: transform 0.3s ease;
    }
    
    .floating {
        transform: translateY(-20px) scale(0.85);
        color: #667eea !important;
        transition: all 0.3s ease;
    }
`;document.head.appendChild(m);
