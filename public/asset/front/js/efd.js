document.addEventListener("DOMContentLoaded", function () {
    const expandBtn = document.querySelector(".expandicon");
    const parent = document.querySelector(".imgparentEFDmain");

    expandBtn.addEventListener("click", function () {
        parent.classList.toggle("expanded");
        expandBtn.classList.toggle("active");

        // Toggle Icon (Plus ↔ Minus)
        if (expandBtn.classList.contains("active")) {
            expandBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#ffffff" d="M256 128C256 110.3 241.7 96 224 96C206.3 96 192 110.3 192 128L192 192L128 192C110.3 192 96 206.3 96 224C96 241.7 110.3 256 128 256L224 256C241.7 256 256 241.7 256 224L256 128zM128 384C110.3 384 96 398.3 96 416C96 433.7 110.3 448 128 448L192 448L192 512C192 529.7 206.3 544 224 544C241.7 544 256 529.7 256 512L256 416C256 398.3 241.7 384 224 384L128 384zM448 128C448 110.3 433.7 96 416 96C398.3 96 384 110.3 384 128L384 224C384 241.7 398.3 256 416 256L512 256C529.7 256 544 241.7 544 224C544 206.3 529.7 192 512 192L448 192L448 128zM416 384C398.3 384 384 398.3 384 416L384 512C384 529.7 398.3 544 416 544C433.7 544 448 529.7 448 512L448 448L512 448C529.7 448 544 433.7 544 416C544 398.3 529.7 384 512 384L416 384z"/></svg>`
        } else {
            expandBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#ffffff" d="M128 96C110.3 96 96 110.3 96 128L96 224C96 241.7 110.3 256 128 256C145.7 256 160 241.7 160 224L160 160L224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L128 96zM160 416C160 398.3 145.7 384 128 384C110.3 384 96 398.3 96 416L96 512C96 529.7 110.3 544 128 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480L160 416zM416 96C398.3 96 384 110.3 384 128C384 145.7 398.3 160 416 160L480 160L480 224C480 241.7 494.3 256 512 256C529.7 256 544 241.7 544 224L544 128C544 110.3 529.7 96 512 96L416 96zM544 416C544 398.3 529.7 384 512 384C494.3 384 480 398.3 480 416L480 480L416 480C398.3 480 384 494.3 384 512C384 529.7 398.3 544 416 544L512 544C529.7 544 544 529.7 544 512L544 416z"/></svg>`
        }
    });
});
 // Library Modal
    function openLibraryModal() {
        document.getElementById('libraryModal').classList.remove('hidden');
        renderHome();
    }
    function closeLibraryModal() {
        document.getElementById('libraryModal').classList.add('hidden');
    }

    // Reception Modal
    /*function openReceptionModal() {
        current = 0;
        showStep();
        document.getElementById('receptionModal').classList.remove('hidden');
        nextBtn.disabled = false;
        nextBtn.innerText = "Next";

    }*/
   function openReceptionModal() {
    current = 0;
    showStep();

    document.getElementById('receptionModal').classList.remove('hidden');

    // FIX: reset buttons
    const nextBtn = document.getElementById("nextBtn");
    const backBtn = document.getElementById("backBtn");
    const finishBtn = document.getElementById("finishBtn");

    nextBtn.style.display = "block";   // show Next button again
    nextBtn.disabled = false;          // enable it
    nextBtn.innerText = "Next";        // reset label

    backBtn.disabled = true;           // back disabled on first step
    finishBtn.style.display = "none";  // hide finish button
}

    function closeReceptionModal() {
        document.getElementById('receptionModal').classList.add('hidden');
    }

    // Door Modal
    function openDoorModal() {
        document.getElementById('doorModal').classList.remove('hidden');
        // Lazily load poster images only now that the modal is actually opened,
        // instead of eagerly loading every poster image on page load.
        document.querySelectorAll('#doorModal img[data-src]').forEach(function (img) {
            img.src = img.dataset.src;
        });
        // 🎯 CALL REWARD API
        fetch('/award-poster-room', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        });
    }
    function closeDoorModal() {
        document.getElementById('doorModal').classList.add('hidden');
    }
    //Women Text
        const steps = [
        `<p>Welcome to the Financial Education Department, ${window.citizenName} ! I'm Ms. Rodriguez, and I'm here to help you navigate your financial learning journey. This department is your personal hub for building a strong foundation in financial literacy.</p>`,

        `<p>See that box above? That's your Activity Center. You'll find <b>Required Activities</b> that will guide you through essential financial concepts, and <b>Optional Activities</b> where you can earn Badge Points while exploring topics that interest you. The more you engage, the more you learn—and the more badges you collect!</p>`,

        `<p>Let me show you around! To your left, you'll find our <b>Library</b>—it has modules and activities to help you learn at your own pace.And over there? That's our <b>Posters Room</b>, with visual guides that make financial concepts easier to understand. Feel free to visit both whenever you need them!</p>`,

        `<p>"I know someone named Robert. He's 87 years old now. He worked as a postal worker his entire life—never made more than <b>45,000 Zed</b> a year. But Robert understood something important: he saved 15% of every paycheck and let compound interest do its work. Today, he has over <b>800,000 Zed</b>. He travels, helps his grandchildren with college, and never worries about medical bills. His secret? He started at 25 and never stopped."</p>`,

        `<p>"Then there's Margaret. She's 82 and also worked her whole life as a teacher. Good salary, but she never budgeted. Every promotion meant a bigger apartment, a nicer car. She retired with <b>12,000 Zed</b> in savings. Now she lives with her daughter, depends on social security, and had to sell her belongings when she needed a surgery last year. Same generation as Robert. Very different choices. Very different lives."</p>`,

        `<p>"Let me tell you about Marcus. He's 40 years old and currently serving time for fraud. It started innocently—he couldn't pay his rent, so he wrote a bad check. Then another to cover that one. Then he started using other people's information to open accounts. He told himself it was temporary, that he'd pay it all back. He owed <b>67,000 Zed</b> when they arrested him. He had no emergency fund, no budget, no plan. Now he has a criminal record and his two kids visit him behind glass."</p>`,

        `<p>"There's also Sarah, 45 years old. She declared bankruptcy three years ago. Not because of medical bills or bad luck—because she never tracked her spending. Small purchases here and there. 'Just <b>50 Zed</b>' turned into <b>3,000 Zed</b> a month she didn't have. The collection calls started. Her car was repossessed while she was at work. She lost her apartment. Her credit score is 490. She can't rent a decent place now, can't get a car loan, and pays everything in cash because no bank will work with her."</p>`,

        `<p>"Now meet David. He's 38, same age Sarah was when everything collapsed. But David started budgeting at 30. He tracked every expense, built a six-month emergency fund, and invested the rest. Last year, he lost his job during company layoffs. You know what happened? Nothing dramatic. He had time to find a better position. He didn't miss a rent payment. His kids didn't know anything was wrong. His emergency fund protected him. That's the difference between a crisis and an inconvenience."</p>`,

        `<p>"I met Linda last month. She's 55 and just realized she has <b>23,000 Zed</b> saved for retirement. She has maybe 10 working years left. She always thought she'd 'start saving later.' Later became now, and now she's terrified. She calculated that she'll need to live on about <b>900 Zed</b> a month when she retires. Her current rent is <b>1,400 Zed</b>. She's not sure what she'll do. She can't go back in time. That's the thing about compound interest—it works magnificently if you start early. It works barely at all if you start late."</p>`,

         `<p>"But here's James. He's 56 and started saving seriously at 35. Not early, but not too late. He set aside 20% of his income—more than Robert because he started later. He drove older cars, lived below his means, and never wavered. Today, he has <b>620,000 Zed</b>. He'll retire comfortably at 65. He made up for lost time with discipline. It wasn't easy, but he understood something: every year you wait makes it harder. He didn't want to be Linda, so he made different choices."</p>`,

        `<p class='mb-2'>"Here's what I've learned watching people for 30 years in this department:"</p>
          <p class='mb-2'>Your financial decisions today are writing your life story for tomorrow.</p><ol class="list-decimal list-inside">
          <li> Robert is 87 and free.</li>
          <li> Margaret is 82 and dependent.</li>
          <li> Marcus is 40 and imprisoned.</li>
          <li> Sarah is 45 and starting over from nothing.</li>
          <li> David is 38 and secure.</li>
          <li> Linda is 55 and scared.</li>
          <li> James is 56 and confident.</li>
          <li> Same economy. Same world. Different choices.</li>
          <li> ${window.citizenName}, the activities in this department? They're not just lessons.</li>
          <li> They're the difference between these stories.</li>
          <li> Choose which person you want to be.</li><ol>`
    ];

    let current = 0;

    function showStep() {
        const box = document.getElementById("displayText");
        box.classList.remove("show");

        setTimeout(() => {
            box.innerHTML = steps[current];
            box.classList.add("show");
        }, 200);
    }

    showStep(); // first load

    // NEXT
    document.getElementById("nextBtn").addEventListener("click", function () {
        if (current < steps.length - 1) {
            current++;
            showStep();
        }

        document.getElementById("backBtn").disabled = current === 0;

        if (current === steps.length - 1) {
            this.disabled = true;
            this.innerText = "Completed";
            // CALL YOUR FUNCTION HERE
            //closeReceptionModal();
            // HIDE BUTTON
            this.style.display = "none";
            // SHOW FINISH BUTTON
          document.getElementById("finishBtn").style.display = "block";
        }
    });

    // BACK
    /*document.getElementById("backBtn").addEventListener("click", function () {
        if (current > 0) {
            current--;
            showStep();
        }

        document.getElementById("nextBtn").disabled = false;
        document.getElementById("nextBtn").innerText = "Next";

        this.disabled = current === 0;
    });*/
document.getElementById("backBtn").addEventListener("click", function () {
    if (current > 0) {
        current--;
        showStep();
    }

    const nextBtn = document.getElementById("nextBtn");
    const finishBtn = document.getElementById("finishBtn");

    // ENABLE & SHOW NEXT BUTTON WHEN GOING BACK
    nextBtn.style.display = "block";
    nextBtn.disabled = false;
    nextBtn.innerText = "Next";

    // HIDE FINISH BUTTON WHEN NOT ON LAST STEP
    finishBtn.style.display = "none";

    // disable Back button on step 0
    this.disabled = current === 0;
});

    //Women Text