  // Apply inline styles to inputs on mobile
  if (window.innerWidth <= 768) {
    const inputs = document.querySelectorAll('#box input:not([type="checkbox"])');
    inputs.forEach(input => {
      // input.style.flex = 'none';
      // input.style.width = '80%';
      input.style.height = '52px'; // mobile-friendly height
      input.style.boxSizing = 'border-box';
      input.style.padding = '0';
      input.style.fontSize = '16px';
    });
  }