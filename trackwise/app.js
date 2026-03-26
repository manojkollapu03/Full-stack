// app.js — TrackWise JS
'use strict';
let barChart=null, donutChart=null;

function initBarChart(monthlyData){
    const ctx=document.getElementById('expenseChart');
    if(!ctx) return;
    const short=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const full=['January','February','March','April','May','June','July','August','September','October','November','December'];
    const vals=short.map((m,i)=>monthlyData[full[i]]||monthlyData[m]||0);
    const max=Math.max(...vals,1);
    barChart=new Chart(ctx,{type:'bar',data:{labels:short,datasets:[{data:vals,backgroundColor:vals.map(v=>v===max?'#3b82f6':'#bfdbfe'),borderColor:vals.map(v=>v===max?'#2563eb':'#93c5fd'),borderWidth:1,borderRadius:5,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,animation:{duration:600},plugins:{legend:{display:false},tooltip:{backgroundColor:'#fff',borderColor:'#e5e7eb',borderWidth:1,titleColor:'#6b7280',bodyColor:'#111827',bodyFont:{family:'Inter',size:13,weight:'600'},padding:10,displayColors:false,callbacks:{label:c=>' $'+c.parsed.y.toLocaleString()}}},scales:{x:{grid:{display:false},border:{display:false},ticks:{color:'#9ca3af',font:{family:'Inter',size:11.5}}},y:{grid:{color:'#f3f4f6'},border:{display:false},ticks:{color:'#9ca3af',font:{family:'Inter',size:11},callback:v=>'$'+v.toLocaleString(),maxTicksLimit:5}}}}});
}

function initDonutChart(spending){
    const ctx=document.getElementById('donutChart');
    if(!ctx) return;
    const colors={Food:'#f97316',Utilities:'#3b82f6',Travel:'#06b6d4',Entertainment:'#a855f7',Healthcare:'#10b981',Shopping:'#f43f5e',Education:'#eab308',Income:'#22c55e',Others:'#94a3b8'};
    const labels=Object.keys(spending);
    const data=labels.map(k=>spending[k]);
    const clrs=labels.map(k=>colors[k]||'#94a3b8');
    if(!labels.length) return;
    donutChart=new Chart(ctx,{type:'doughnut',data:{labels,datasets:[{data,backgroundColor:clrs.map(c=>c+'cc'),borderColor:clrs,borderWidth:1.5,hoverOffset:5}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',animation:{duration:700},plugins:{legend:{display:false},tooltip:{backgroundColor:'#fff',borderColor:'#e5e7eb',borderWidth:1,titleColor:'#6b7280',bodyColor:'#111827',bodyFont:{family:'Inter',size:13,weight:'700'},padding:10,callbacks:{label:c=>' $'+c.parsed.toLocaleString()}}}}});
}

function switchChart(type,btn){
    document.querySelectorAll('.tgl').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    if(!barChart) return;
    const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const years=['2021','2022','2023','2024','2025','2026'];
    const labels=type==='yearly'?years:months;
    const data=labels.map(()=>Math.floor(Math.random()*2000+200));
    const max=Math.max(...data);
    barChart.data.labels=labels;
    barChart.data.datasets[0].data=data;
    barChart.data.datasets[0].backgroundColor=data.map(v=>v===max?'#3b82f6':'#bfdbfe');
    barChart.data.datasets[0].borderColor=data.map(v=>v===max?'#2563eb':'#93c5fd');
    barChart.update();
}

function showToast(msg,type='success'){
    let t=document.querySelector('.toast');
    if(t) t.remove();
    t=document.createElement('div');
    t.className=`toast ${type}`;
    t.innerHTML=`<span>${type==='success'?'✓':'✕'}</span><span>${msg}</span>`;
    document.body.appendChild(t);
    setTimeout(()=>t.classList.add('show'),30);
    setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),400);},3000);
}
