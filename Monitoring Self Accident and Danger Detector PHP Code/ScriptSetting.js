const settingList = document.getElementById("settingList");
const numberForm = document.getElementById("numberForm");
const messageForm = document.getElementById("messageForm");
const numberInput = document.getElementById("numberInput");
const messageInput = document.getElementById("messageInput");
const addNumberBtn = document.getElementById("addNumberBtn");
const addMessageBtn = document.getElementById("addMessageBtn");

numberForm.addEventListener("submit", addSetting);
messageForm.addEventListener("submit", addSetting);

function addSetting(event) {
    event.preventDefault();
    const inputField = event.target === numberForm ? numberInput : messageInput;
    const inputValue = inputField.value;
    if (inputValue.trim() === "") return;
    const li = document.createElement("li");
    li.innerHTML = `
        <span>${inputValue}</span>
        <button class="edit-btn">Edit</button>
        <button class="delete-btn">Delete</button>
    `;
    settingList.appendChild(li);
    inputField.value = "";
    addEditListener(li);
    addDeleteListener(li);
    if (event.target === numberForm) {
        addNumberBtn.disabled = true;
    } else {
        addMessageBtn.disabled = true;
    }
}

function addEditListener(li) {
    const editBtn = li.querySelector(".edit-btn");
    editBtn.addEventListener("click", function () {
        const newSetting = prompt("Enter new setting:");
        if (newSetting === null || newSetting.trim() === "") return;
        li.querySelector("span").textContent = newSetting;
    });
}

function addDeleteListener(li) {
    const deleteBtn = li.querySelector(".delete-btn");
    deleteBtn.addEventListener("click", function () {
        li.remove();
        if (li.parentNode === settingList) {
            addNumberBtn.disabled = false;
        } else {
            addMessageBtn.disabled = false;
        }
    });
}
