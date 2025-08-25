document.addEventListener("DOMContentLoaded", () => {
  const addItemForm = document.getElementById("add-item-form");
  const itemList = document.querySelector(".item-list");
  const editModal = document.getElementById("edit-modal");
  const closeModalBtn = document.getElementById("close-modal");
  const editItemForm = document.getElementById("edit-item-form");

  if (addItemForm) {
    addItemForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(addItemForm);
      try {
        const response = await fetch("add_item.php", {
          method: "POST",
          body: formData,
        });
        const result = await response.json();
        if (result.success) {
          window.location.reload();
        } else {
          alert("エラー: " + (result.message || "項目の追加に失敗しました。"));
        }
      } catch (error) {
        console.error("項目追加エラー:", error);
        alert("エラー: 項目の追加中に問題が発生しました。");
      }
    });
  }

  if (itemList) {
    itemList.addEventListener("click", async (e) => {
      const target = e.target;
      const itemElement = target.closest(".item");
      if (!itemElement) return;
      const itemId = itemElement.dataset.id;

      if (target.closest(".delete-btn")) {
        if (!confirm("本当にこの項目を削除しますか？")) {
          return;
        }
        const formData = new FormData();
        formData.append("id", itemId);

        try {
          const response = await fetch("delete_item.php", {
            method: "POST",
            body: formData,
          });
          const result = await response.json();
          if (result.success) {
            itemElement.remove();
            window.location.reload();
          } else {
            alert(
              "エラー: " + (result.message || "項目の削除に失敗しました。")
            );
          }
        } catch (error) {
          console.error("項目削除エラー:", error);
          alert("エラー: 項目の削除中に問題が発生しました。");
        }
      }

      if (target.closest(".check-progress-btn")) {
        const button = target.closest(".check-progress-btn");
        const currentLevel = button.dataset.level;
        let newLevel;

        if (currentLevel === "studying") {
          newLevel = "learned";
        } else if (currentLevel === "learned") {
          newLevel = "studying";
        } else {
          return;
        }

        const formData = new FormData();
        formData.append("id", itemId);
        formData.append("level", newLevel);

        try {
          const response = await fetch("update_level.php", {
            method: "POST",
            body: formData,
          });
          const result = await response.json();
          if (result.success) {
            window.location.reload();
          } else {
            alert(
              "エラー: " +
                (result.message || "ステータスの更新に失敗しました。")
            );
          }
        } catch (error) {
          console.error("ステータス更新エラー:", error);
          alert("エラー: ステータスの更新中に問題が発生しました。");
        }
      }

      if (target.closest(".edit-btn")) {
        console.log("Mở modal chỉnh sửa cho item ID:", itemId);

        const japaneseText = itemElement.querySelector(
          ".item-japanese-text"
        ).textContent;
        const readingElement = itemElement.querySelector(".item-reading");
        const meaningElement = itemElement.querySelector(".item-meaning");
        const exampleElement = itemElement.querySelector(".item-example");
        const typeElement = itemElement.querySelector(".item-type");
        const categoryElement = itemElement.querySelector(".item-category");
        const levelElement = itemElement.querySelector(".item-level");

        const reading = readingElement
          ? readingElement.textContent.replace("読み方: ", "").trim()
          : "";
        const meaning = meaningElement
          ? meaningElement.textContent.replace("意味: ", "").trim()
          : "";
        const example = exampleElement
          ? exampleElement.textContent.replace("例文: ", "").trim()
          : "";

        const type =
          typeElement.textContent.trim() === "単語"
            ? "vocabulary"
            : typeElement.textContent.trim() === "文法"
            ? "grammar"
            : "kanji";
        const category = categoryElement
          ? categoryElement.textContent.trim()
          : "";
        const level =
          levelElement.textContent.trim() === "覚えた"
            ? "learned"
            : levelElement.textContent.trim() === "勉強中"
            ? "studying"
            : "forgotten";

        document.getElementById("edit-item-id").value = itemId;
        document.getElementById("edit-type").value = type;
        document.getElementById("edit-category").value = category;
        document.getElementById("edit-level").value = level;
        document.getElementById("edit-japanese-text").value = japaneseText;
        document.getElementById("edit-reading").value = reading;
        document.getElementById("edit-meaning").value = meaning;
        document.getElementById("edit-example-sentence").value = example;

        editModal.style.display = "flex";
      }
    });
  }

  if (closeModalBtn) {
    closeModalBtn.addEventListener("click", () => {
      editModal.style.display = "none";
    });
  }

  if (editModal) {
    window.addEventListener("click", (e) => {
      if (e.target === editModal) {
        editModal.style.display = "none";
      }
    });
  }

  if (editItemForm) {
    editItemForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(editItemForm);

      console.log("Đang gửi yêu cầu cập nhật cho item ID:", formData.get("id"));

      try {
        const response = await fetch("update_item.php", {
          method: "POST",
          body: formData,
        });
        const result = await response.json();
        if (result.success) {
          editModal.style.display = "none";
          window.location.reload();
        } else {
          alert("エラー: " + (result.message || "項目の更新に失敗しました。"));
        }
      } catch (error) {
        console.error("項目更新エラー:", error);
        alert("エラー: 項目の更新中に問題が発生しました。");
      }
    });
  }
});
