<template>
  <section class="about-section">
    <div class="about-container">
      <h2>About Razz Rel Events</h2>
      <div class="about-content">
        <div class="about-text">
          <p>
            Welcome to Razz Rel Events, your premier event planning partner. We
            specialize in creating unforgettable moments for weddings, debuts,
            christenings, and parties.
          </p>
          <div class="features">
            <div class="feature">
              <i class="fas fa-star"></i>
              <h3>Expert Planning</h3>
              <p>Professional event coordinators dedicated to your vision</p>
            </div>
            <div class="feature">
              <i class="fas fa-heart"></i>
              <h3>Personalized Service</h3>
              <p>Customized packages to match your unique style</p>
            </div>
            <div class="feature">
              <i class="fas fa-check-circle"></i>
              <h3>Quality Assurance</h3>
              <p>Premium vendors and services for your special day</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.about-section {
  padding: 4rem 2rem;
  background-color: #f8f9fa;
}

.about-container {
  max-width: 1200px;
  margin: 0 auto;
}

h2 {
  text-align: center;
  color: #333;
  margin-bottom: 2rem;
  font-size: 2rem;
}

.about-content {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.about-text {
  flex: 1;
}

.features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.feature {
  text-align: center;
  padding: 1.5rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.feature:hover {
  transform: translateY(-5px);
}

.feature i {
  font-size: 2rem;
  color: #aec0c2;
  margin-bottom: 1rem;
}

.feature h3 {
  margin-bottom: 0.5rem;
  color: #333;
}

.feature p {
  color: #666;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .about-content {
    flex-direction: column;
  }

  .features {
    grid-template-columns: 1fr;
  }
}
</style> 