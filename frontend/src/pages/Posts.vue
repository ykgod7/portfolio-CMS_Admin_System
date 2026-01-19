<template>
  <div class="page">
    <header class="page-header">
      <div>
        <h1 class="title">Posts</h1>
        <p class="desc">게시글 목록</p>
      </div>

      <div class="actions">
        <input
          v-model="q"
          class="input"
          type="search"
          placeholder="검색"
          @keyup.enter="refetch"
        />
        <button class="btn new" @click="goNew">+ 새 글</button>
      </div>
    </header>

    <main class="content">
      <div v-if="loading" class="state">불러오는 중...</div>
      <div v-else-if="error" class="state error">
        {{ error }}
        <button class="btn ghost" @click="refetch">다시 시도</button>
      </div>

      <ul v-else class="list">
        <li v-for="(p, idx) in posts" :key="p.id" class="item" @click="goDetail(p.id)">
          <div class="item-main">
            <h3 class="item-title">{{ p.title }}</h3>
            <p class="item-meta">
              <span>#{{ idx + 1 }}</span>
              <span>·</span>
              <span>{{ formatDate(p.created_at) }}</span>
              <span v-if="p.author_name">· {{ p.author_name }}</span>
            </p>
          </div>
          <span class="chev">›</span>
        </li>
      </ul>

      <!-- 페이지네이션 자리 -->
      <footer class="pager">
        <button class="btn ghost" :disabled="page <= 1" @click="page--">이전</button>
        <span class="pager-text">Page {{ page }}</span>
        <button class="btn ghost" :disabled="page === maxPage" @click="page++">다음</button>
      </footer>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { api } from "../lib/api";

const router = useRouter();

const posts = ref([]);
const loading = ref(false);
const error = ref("");

const q = ref("");
const page = ref(1);
const maxPage = ref(null);
const size = ref(20);

const goNew = () => router.push("/posts/new");
const goDetail = (id) => router.push({ name: 'Post', params: { id } });

const formatDate = (v) => {
  if (!v) return "";
  const d = new Date(v);
  return isNaN(d.getTime()) ? String(v) : d.toLocaleString("ko-KR");
};

const fetchPosts = async () => {
  loading.value = true;
  error.value = "";

  try {
    const res = await api.get("/posts.php", {
      params: {
        q: q.value || "", 
        page: page.value,
        size: size.value
      }
    });
    posts.value = res.data.data.items ?? [];
    const total = res.data.data.total;

    maxPage.value = Math.ceil(total / size.value);

  } catch (e) {
    error.value =
      e?.response?.data?.message ||
      e?.message ||
      "게시글 목록을 불러오지 못했습니다.";
  } finally {
    loading.value = false;
  }
};

const refetch = () => {
  page.value = 1;
  fetchPosts();
};

onMounted(fetchPosts);

watch(page, () => fetchPosts());
</script>

<style lang="scss" scoped>
.page {
  width: 100%;  
  margin: 0 auto;
  padding: 24px 0;

  &-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 16px;
    margin-bottom: 16px;
  }
}

.title {
  margin: 0;
  font-size: 28px;
}

.desc {
  margin: 6px 0 0;
  color: #666;
}

.actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.input {
  min-width: 220px;
  height: 36px;
  padding: 0 12px;
  border: 1px solid #ddd;
  border-radius: 10px;
}

.btn {
  height: 36px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1px solid #111;
  background: #111;
  color: #fff;
  cursor: pointer;

  &.new {
    width: 110px;
  }

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

.content {
  padding: 14px;
  border: 1px solid #eee;
  border-radius: 14px;
  background: #fff;
}

.state {
  padding: 18px;
  color: #555;

  &.error {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #b00020;
  }
}

.list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border: 1px solid #eee;
  border-radius: 12px;
  cursor: pointer;

  &:hover {
    border-color: #ddd;
  }

  &-title {
    margin: 0;
    font-size: 16px;
  }

  &-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 6px 0 0;
    font-size: 13px;
    color: #777;
  }
}

.chev {
  font-size: 20px;
  color: #999;
}

.pager {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 14px;

  &-text {
    color: #666;
  }
}

</style>
