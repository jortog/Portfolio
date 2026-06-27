// ============================================================
// public/js/app.js — App-specific Frontend JS
// ============================================================

// ── Ask Me (matches keyword system in index.php inline script) ─
const info = {
    name: "John Ronie Ramiro",
    age: "20 years old",
    address: "Longos Kalayaan, Laguna",
    course: "Bachelor of Science in Computer Science",
    school: "Polytechnic University of the Philippines"
};
const hobbies = ["Maglaro ng video games", "Manuod ng movies o series", "Gumala", "at Makinig kay frank dagat"];
const dailyRoutine = ["Gising at mag prepare sa gagawin sa buong araw", "Pumasok sa school o kaya online class", "Mag-aral o kaya gumawa ng pendings", "at Mag chill time pag tapos ng scool at tumambay with mga og"];
const keywords = {
    name:    ["name","who are you","anong pangalan mo","pangalan mo","sino ka"],
    age:     ["age","how old","years old","ilan taon ka","gaano ka katanda","ilang taon ka na"],
    address: ["address","where do you live","location","where are you from","taga saan ka","saan ka nakatira","taga san ka"],
    course:  ["course","program","what are you studying","ano course mo"],
    school:  ["school","university","where do you study","saan ka nag-aaral","ano school mo","san ka nag aaral"],
    hobbies: ["hobby","hobbies","what do you do for fun","interests","ano ang libangan mo","ano trip mo","ano mga trip mo sa buhay"],
    routine: ["routine","daily","schedule","what do you do daily","ano ginagawa mo araw-araw","pano natakbo araw mo"],
    valo:    ["valo","valorant","game","gamer","jortog"]
};

function answerQuestion() {
    const qInput = document.getElementById('userQuestion');
    const box    = document.getElementById('answer');
    if (!qInput || !box) return;
    const q   = qInput.value.trim().toLowerCase();
    let ans = "Wala pa akong sagot diyan grabe ka mag tanong... yung tinatanong mo kasi dapat tungkol lang sakin.";
    if (!q) { ans = "Please type a question first."; }
    else if (keywords.name.some(k => q.includes(k)))    ans = "Ako si " + info.name + ".";
    else if (keywords.age.some(k => q.includes(k)))     ans = "Ako ay " + info.age + ".";
    else if (keywords.address.some(k => q.includes(k))) ans = "Ako ay taga " + info.address + ".";
    else if (keywords.course.some(k => q.includes(k)))  ans = info.course + " ang course ko, pre.";
    else if (keywords.school.some(k => q.includes(k)))  ans = "Sa " + info.school + " ako nag-aaral.";
    else if (keywords.hobbies.some(k => q.includes(k))) ans = "Eto mga trip ko: " + hobbies.join(", ") + ".";
    else if (keywords.routine.some(k => q.includes(k))) ans = "Eto gawain ko araw araw: " + dailyRoutine.join(" → ") + ".";
    else if (keywords.valo.some(k => q.includes(k)))    ans = "G sa Valo? Add mo ko: Jortog #123 🎮";

    box.textContent = ans;
    box.classList.remove('show');
    void box.offsetWidth;
    box.classList.add('show');
    qInput.value = '';
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('submitBtn');
    const qIn = document.getElementById('userQuestion');
    if (btn) btn.addEventListener('click', answerQuestion);
    if (qIn) qIn.addEventListener('keypress', e => { if (e.key === 'Enter') answerQuestion(); });

    // IntersectionObserver for .section fade-in
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.section').forEach(s => observer.observe(s));

    // Auto-dismiss flash messages
    document.querySelectorAll('.flash').forEach(f => {
        setTimeout(() => f.remove(), 4500);
    });
});
