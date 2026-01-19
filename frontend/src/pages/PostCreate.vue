<template>
  <div class="page">
    <header class="page-header">
      <div class="page-header__text">
        <h1 class="title">새 게시글 작성</h1>
        <p class="desc">제목과 내용을 입력해 게시글을 생성합니다.</p>
      </div>

      <div class="actions">
        <button class="btn ghost" type="button" @click="goList">목록으로</button>
      </div>
    </header>

    <main class="content">
      <form class="form" @submit.prevent="onSubmit">
        <div class="field">
          <label class="label" for="title">제목</label>
          <input
            id="title"
            v-model.trim="form.title"
            class="input"
            type="text"
            placeholder="제목을 입력하세요"
            maxlength="120"
            autocomplete="off"
          />
          <p class="hint">
            {{ form.title.length }}/120
          </p>
        </div>

        <div class="field">
          <label class="label" for="content">내용</label>
          <textarea
            id="content"
            v-model.trim="form.content"
            class="textarea"
            rows="12"
            placeholder="내용을 입력하세요"
          />
          <p class="hint">
            {{ form.content.length }}자
          </p>
        </div>

        <p v-if="error" class="error">{{ error }}</p>

        <div class="footer">
          <button class="btn ghost" type="button" @click="onReset" :disabled="loading">
            초기화
          </button>
          <button class="btn" type="submit" :disabled="!canSubmit || loading">
            {{ loading ? "등록 중..." : "등록" }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from "vue";
import { useRouter } from "vue-router";
import { api } from "../lib/api";

const router = useRouter();

const loading = ref(false);
const error = ref("");

const form = reactive({
  title: "",
  content: "",
});

const canSubmit = computed(() => {
  const t = form.title.trim();
  const c = form.content.trim();
  return t.length >= 2 && c.length >= 5;
});

function goList() {
  router.push("/posts");
}

function onReset() {
  error.value = "";
  form.title = "";
  form.content = "";
}

async function onSubmit() {
  error.value = "";

  if (!canSubmit.value) {
    error.value = "제목(2자 이상)과 내용(5자 이상)을 입력해 주세요.";
    return;
  }

  loading.value = true;

  try {
    await api.post("/posts.php", {
      title: form.title.trim(),
      content: form.content.trim(),
    });

    // 생성 후 목록으로 이동
    router.push("/posts");
  } catch (e) {
    error.value =
      e?.response?.data?.message ||
      e?.message ||
      "게시글 생성에 실패했습니다.";
  } finally {
    loading.value = false;
  }
}
</script>

<style lang="scss" scoped>
.page {
  max-width: 980px;
  margin: 0 auto;
  padding: 24px 0;
}

.page-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-end;
  margin-bottom: 16px;

  &__text {
    min-width: 0;
  }
}

.title {
  font-size: 28px;
  margin: 0;
}

.desc {
  margin: 6px 0 0;
  color: #666;
}

.actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.content {
  border: 1px solid #eee;
  border-radius: 14px;
  padding: 16px;
  background: #fff;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.label {
  font-size: 14px;
  color: #333;
  font-weight: 600;
}

.input {
  height: 40px;
  padding: 0 12px;
  border: 1px solid #ddd;
  border-radius: 10px;
  outline: none;

  &:focus {
    border-color: #111;
  }
}

.textarea {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 10px;
  outline: none;
  resize: vertical;

  &:focus {
    border-color: #111;
  }
}

.hint {
  margin: 0;
  font-size: 12px;
  color: #888;
}

.error {
  margin: 2px 0 0;
  color: #b00020;
  font-size: 13px;
}

.footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 6px;
}

.btn {
  height: 36px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1px solid #111;
  background: #111;
  color: #fff;
  cursor: pointer;

  &.ghost {
    background: transparent;
    color: #111;
    border-color: #ddd;
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}
</style>
