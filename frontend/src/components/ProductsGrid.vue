<template>
  <div>
    <h1>Каталог товаров</h1>
    <pre>{{ books }}</pre>
    <!-- Временный вывод всего JSON -->
    <div class="grid">
      <div v-for="book in books" :key="book.id" class="card">
        <img
          :src="
            book.coverImage ? `/uploads/covers/${book.coverImage}` : 'https://placehold.co/300x200'
          "
          alt="Обложка"
        />
        <h2>{{ book.title }}</h2>
        <p><strong>Автор:</strong> {{ book.author }}</p>
        <p>{{ book.genre || 'Жанр неизвестен' }}</p>
        <button @click="goToBook(book.id)">Подробнее</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      books: [], // Здесь будет полный ответ от API
    }
  },
  mounted() {
    this.fetchBooks()
  },
  methods: {
    async fetchBooks() {
      try {
        const response = await axios.get('/books')
        console.log('Ответ от API:', response.data) // Вывод в консоль
        this.books = response.data // Присваиваем данные
      } catch (error) {
        console.error('Ошибка при загрузке данных:', error.response || error.message)
      }
    },
  },
}
</script>

<style scoped>
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}
.card {
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 16px;
  text-align: center;
}
.card img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
}
</style>
