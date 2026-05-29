<template>
  <div class="landing-ref">
    <!-- Hero: light area (reference style, light theme) -->
    <section class="hero-panel" aria-labelledby="hero-heading">
      <v-container fluid class="px-4 px-sm-6 py-10 py-md-14" style="max-width: 1200px">
        <v-row align="center" class="ga-8 ga-md-4">
          <v-col cols="12" md="6" class="order-2 order-md-1 text-center text-md-start">
            <div class="hero-decor d-flex align-center justify-center justify-md-start ga-5 mb-5">
              <div class="decor-dots" aria-hidden="true">
                <span v-for="i in 5" :key="i" class="decor-dot" />
              </div>
              <div class="decor-pluses d-flex ga-1" aria-hidden="true">
                <span class="decor-plus">+</span>
                <span class="decor-plus">+</span>
                <span class="decor-plus">+</span>
              </div>
            </div>
            <h1 id="hero-heading" class="hero-headline text-h4 text-sm-h3 text-md-h2 font-weight-bold mb-5">
              Where money meets trust.
            </h1>
            <p class="hero-lead text-body-1 mb-8">
              Borrow with clarity and manage loans with confidence. LoanHub keeps applications, approvals, and
              repayments in one simple place—so you always know what’s next, in Philippine pesos (₱) you understand.
            </p>
            <v-btn
              to="/register"
              size="x-large"
              min-height="52"
              rounded="lg"
              variant="flat"
              color="primary"
              class="hero-cta text-none px-8"
              prepend-icon="mdi-arrow-right-circle-outline"
            >
              Know more
            </v-btn>
          </v-col>
          <v-col cols="12" md="6" class="order-1 order-md-2">
            <div class="hero-visual">
              <div class="hero-image-frame rounded-xl overflow-hidden elevation-4">
                <img
                  class="hero-img"
                  src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=960&q=80"
                  width="640"
                  height="480"
                  alt="People planning finances together with a laptop"
                  loading="eager"
                  decoding="async"
                />
              </div>
              <div class="float-badge float-badge--tl" aria-hidden="true">
                <span class="float-badge-inner">₱</span>
              </div>
              <div class="float-badge float-badge--br" aria-hidden="true">
                <span class="float-badge-inner">₱</span>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Navy feature bar (rounded top-left) -->
    <section id="services" class="features-navy" aria-labelledby="services-heading">
      <v-container fluid class="px-4 px-sm-6 py-12 py-md-14" style="max-width: 1200px">
        <h2 id="services-heading" class="sr-only">Our services</h2>
        <v-row class="ga-8 ga-md-0">
          <v-col v-for="item in services" :key="item.title" cols="12" md="4">
            <div class="feature-block text-center text-md-start px-md-2">
              <v-avatar size="64" class="feature-icon-wrap mb-4">
                <v-icon :icon="item.icon" size="34" color="white" />
              </v-avatar>
              <h3 class="text-h6 font-weight-bold text-white mb-2">{{ item.title }}</h3>
              <p class="text-body-2 mb-0 feature-desc">{{ item.text }}</p>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <section id="about" class="about-section py-14 py-md-16">
      <v-container fluid class="px-4 px-sm-6 text-center" style="max-width: 720px">
        <h2 class="text-h5 font-weight-bold mb-4">About LoanHub</h2>
        <p class="text-body-1 text-medium-emphasis mb-0" style="line-height: 1.7">
          We built LoanHub for lenders and borrowers who want less paperwork stress and more transparency. Customers
          follow their loan journey online; your team handles reviews and updates from a dedicated admin workspace.
        </p>
        <div class="d-flex flex-wrap ga-3 justify-center mt-8">
          <v-btn
            to="/register"
            variant="flat"
            color="primary"
            size="large"
            rounded="lg"
            class="hero-cta text-none"
            prepend-icon="mdi-account-plus"
          >
            Create account
          </v-btn>
          <v-btn
            to="/login"
            variant="outlined"
            color="primary"
            class="text-none"
            size="large"
            rounded="lg"
            prepend-icon="mdi-login"
          >
            Sign in
          </v-btn>
        </div>
      </v-container>
    </section>

    <footer id="contact" class="footer-landing border-t py-10 bg-surface">
      <v-container fluid class="px-4 px-sm-6" style="max-width: 1200px">
        <v-row>
          <v-col cols="12" md="6" class="mb-6 mb-md-0">
            <div class="d-flex align-center ga-2 text-subtitle-1 font-weight-bold mb-2">
              <v-icon icon="mdi-finance" color="primary" size="26" />
              LoanHub
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0" style="max-width: 28rem">
              Questions? Reach your loan officer or support through the contact details your organization provides.
            </p>
          </v-col>
          <v-col cols="12" md="6" class="text-md-end">
            <nav class="d-flex flex-wrap ga-4 justify-md-end mb-3" aria-label="Footer">
              <NuxtLink to="/" class="text-body-2">Home</NuxtLink>
              <a href="#services" class="text-body-2">Services</a>
              <NuxtLink to="/login" class="text-body-2">Sign in</NuxtLink>
              <NuxtLink to="/register" class="text-body-2">Register</NuxtLink>
            </nav>
            <p class="text-caption text-medium-emphasis mb-0">© {{ year }} LoanHub. All rights reserved.</p>
          </v-col>
        </v-row>
      </v-container>
    </footer>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'landing',
})

const year = new Date().getFullYear()

const services = [
  {
    title: 'Personal loans',
    text: 'Apply online, track approval, and see your balance and schedule in pesos—without guesswork.',
    icon: 'mdi-piggy-bank-outline',
  },
  {
    title: 'Business loans',
    text: 'Support for business borrowing with structured applications and clear status for your team.',
    icon: 'mdi-hand-coin-outline',
  },
  {
    title: 'Payments & history',
    text: 'Record and review payments with receipts and running balances, all aligned to PHP.',
    icon: 'mdi-credit-card-outline',
  },
]
</script>

<style scoped>
.landing-ref {
  overflow-x: hidden;
}

/* Light hero band — soft blue wash like reference, adapted for light theme */
.hero-panel {
  background: linear-gradient(
    180deg,
    rgba(var(--v-theme-primary), 0.12) 0%,
    #f0f6ff 35%,
    rgb(var(--v-theme-background)) 100%
  );
  padding-bottom: 3rem !important;
  margin-top: 0 !important;
}

@media (min-width: 960px) {
  .hero-panel {
    padding-bottom: 4rem !important;
  }
}

.hero-headline {
  color: rgb(var(--v-theme-primary));
  line-height: 1.15;
  letter-spacing: -0.02em;
  text-wrap: balance;
}

.hero-lead {
  color: rgb(var(--v-theme-on-surface));
  line-height: 1.7;
  max-width: 36rem;
  margin-left: auto;
  margin-right: auto;
}

@media (min-width: 960px) {
  .hero-lead {
    margin-left: 0;
    margin-right: 0;
  }
}

/* rgb() wrapper required — --v-theme-primary is only "R, G, B" channels */
.hero-cta {
  background: rgb(var(--v-theme-primary)) !important;
  color: rgb(var(--v-theme-on-primary)) !important;
  letter-spacing: 0.03em;
  box-shadow: 0 6px 20px rgba(var(--v-theme-primary), 0.35);
}

.hero-cta :deep(.v-btn__prepend .v-icon),
.hero-cta :deep(.v-btn__content) {
  color: rgb(var(--v-theme-on-primary)) !important;
}

.hero-decor {
  opacity: 0.85;
}

.decor-dots {
  display: flex;
  gap: 6px;
  align-items: center;
}

.decor-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgb(var(--v-theme-primary));
  opacity: 0.5;
}

.decor-dot:nth-child(3) {
  opacity: 1;
  transform: scale(1.15);
}

.decor-plus {
  color: rgb(var(--v-theme-primary));
  font-size: 1.25rem;
  font-weight: 300;
  line-height: 1;
  opacity: 0.7;
}

/* Image + floating ₱ badges */
.hero-visual {
  position: relative;
  max-width: 520px;
  margin: 0 auto;
}

.hero-image-frame {
  position: relative;
  border: 4px solid #fff;
  box-shadow: 0 20px 50px rgba(var(--v-theme-primary), 0.22);
  border-radius: 10px !important;
}

.hero-img {
  display: block;
  width: 100%;
  height: auto;
  vertical-align: middle;
  object-fit: cover;
  aspect-ratio: 4 / 3;
}

.float-badge {
  position: absolute;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 8px 28px rgba(var(--v-theme-primary), 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}

.float-badge--tl {
  top: -12px;
  left: -8px;
}

.float-badge--br {
  bottom: -12px;
  right: -8px;
}

.float-badge-inner {
  font-size: 1.35rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  line-height: 1;
}

/* Navy feature strip — large rounded top-left, overlaps hero slightly */
.features-navy {
  background: rgb(var(--v-theme-primary));
  border-radius: 2.75rem 0 0 0;
  margin-top: -2.5rem;
  position: relative;
  z-index: 2;
  box-shadow: 0 -12px 40px rgba(var(--v-theme-primary), 0.25);
}

@media (max-width: 959px) {
  .features-navy {
    margin-top: -1.5rem;
    border-radius: 2rem 0 0 0;
  }
}

.feature-icon-wrap {
  background: rgba(255, 255, 255, 0.12) !important;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.feature-desc {
  color: rgba(255, 255, 255, 0.88);
  line-height: 1.65;
  max-width: 22rem;
  margin-left: auto;
  margin-right: auto;
}

@media (min-width: 960px) {
  .feature-desc {
    margin-left: 0;
    margin-right: 0;
  }
}

.about-section {
  background: rgb(var(--v-theme-surface));
}

.footer-landing :deep(a) {
  text-decoration: none;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
