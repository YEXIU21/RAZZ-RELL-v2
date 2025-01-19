<template>
  <div class="refund-policy">
    <div class="policy-header">
      <h3>Refund Policy</h3>
      <p class="subtitle">Please review our refund policy before submitting a request</p>
    </div>

    <div class="policy-content">
      <div class="policy-section">
        <h4>
          <i class="fas fa-clock"></i>
          Cancellation Timeline
        </h4>
        <ul>
          <li>
            <span class="highlight">30+ days before event:</span>
            Full refund minus 10% administrative fee
          </li>
          <li>
            <span class="highlight">15-29 days before event:</span>
            70% refund of total payment
          </li>
          <li>
            <span class="highlight">7-14 days before event:</span>
            50% refund of total payment
          </li>
          <li>
            <span class="highlight">Less than 7 days before event:</span>
            No refund (special circumstances may be considered)
          </li>
        </ul>
      </div>

      <div class="policy-section">
        <h4>
          <i class="fas fa-umbrella"></i>
          Special Circumstances
        </h4>
        <ul>
          <li>
            <span class="highlight">Weather Conditions:</span>
            Full refund or rescheduling option for severe weather events
          </li>
          <li>
            <span class="highlight">Medical Emergencies:</span>
            Case-by-case evaluation with proper documentation
          </li>
          <li>
            <span class="highlight">Double Booking:</span>
            Full refund plus 10% compensation
          </li>
          <li>
            <span class="highlight">Service Issues:</span>
            Partial or full refund based on the severity of the issue
          </li>
        </ul>
      </div>

      <div class="policy-section">
        <h4>
          <i class="fas fa-sync-alt"></i>
          Rescheduling Policy
        </h4>
        <ul>
          <li>
            <span class="highlight">First Reschedule:</span>
            Free of charge if requested 14+ days before event
          </li>
          <li>
            <span class="highlight">Additional Reschedules:</span>
            Subject to 15% administrative fee
          </li>
          <li>
            <span class="highlight">Emergency Reschedule:</span>
            Evaluated case-by-case with proper documentation
          </li>
        </ul>
      </div>

      <div class="policy-section">
        <h4>
          <i class="fas fa-money-bill-wave"></i>
          Refund Process
        </h4>
        <ul>
          <li>
            <span class="highlight">Processing Time:</span>
            7-14 business days after approval
          </li>
          <li>
            <span class="highlight">Original Payment Method:</span>
            Refunds are typically processed to the original payment method
          </li>
          <li>
            <span class="highlight">Alternative Methods:</span>
            Bank transfer or GCash available upon request
          </li>
        </ul>
      </div>

      <div class="policy-section">
        <h4>
          <i class="fas fa-exclamation-circle"></i>
          Important Notes
        </h4>
        <ul>
          <li>All refund requests must be submitted through our online system</li>
          <li>Supporting documentation may be required for special circumstances</li>
          <li>Refund amounts are calculated based on the original payment amount</li>
          <li>Administrative fees are non-refundable</li>
          <li>Decisions on special cases are at management's discretion</li>
        </ul>
      </div>

      <div class="contact-support">
        <i class="fas fa-headset"></i>
        <div class="support-content">
          <h4>Need Help?</h4>
          <p>Our support team is here to assist you with any questions about our refund policy</p>
          <button class="support-btn" @click="contactSupport">
            Contact Support
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const contactSupport = () => {
  router.push('/contact');
};
</script>

<style scoped>
.refund-policy {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.policy-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--primary-color-light);
}

.policy-header h3 {
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.subtitle {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.policy-content {
  padding: 1.5rem;
}

.policy-section {
  margin-bottom: 2rem;
}

.policy-section h4 {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--text-color);
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.policy-section h4 i {
  color: var(--primary-color);
  font-size: 1.25rem;
}

.policy-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.policy-section li {
  margin-bottom: 0.75rem;
  padding-left: 1.5rem;
  position: relative;
  line-height: 1.5;
  color: var(--text-muted);
}

.policy-section li::before {
  content: "•";
  position: absolute;
  left: 0;
  color: var(--primary-color);
}

.highlight {
  color: var(--text-color);
  font-weight: 500;
  display: block;
}

.contact-support {
  margin-top: 3rem;
  padding: 2rem;
  background: var(--background-color);
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.contact-support i {
  font-size: 2.5rem;
  color: var(--primary-color);
}

.support-content {
  flex: 1;
}

.support-content h4 {
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.support-content p {
  color: var(--text-muted);
  margin-bottom: 1rem;
}

.support-btn {
  display: inline-flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.support-btn:hover {
  background: var(--primary-color-dark);
}

@media (max-width: 768px) {
  .contact-support {
    flex-direction: column;
    text-align: center;
    padding: 1.5rem;
  }

  .support-content {
    text-align: center;
  }
}
</style> 