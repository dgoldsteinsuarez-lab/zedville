$('.togglePsw').click(function(){
    $(this).toggleClass('active');
    var input = $(this).parent('.pswtoggle').find('input');
    if (input.attr('type') == 'password') {
        input.attr('type', 'text');
    } else {
        input.attr('type', 'password');
    }
});

document.querySelectorAll('.accordion-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const content = button.nextElementSibling;
        button.classList.toggle('open');
        content.classList.toggle('open');
    });
});


// Admin Script
const setup = () => {
  function getSidebarStateFromLocalStorage() {
    // if it already there, use it
    if (window.localStorage.getItem('isSidebarOpen')) {
      return JSON.parse(window.localStorage.getItem('isSidebarOpen'))
    }

    // else return the initial state you want
    return (
     false
    )
  }

  function setSidebarStateToLocalStorage(value) {
    window.localStorage.setItem('isSidebarOpen', value)
  }

return {
      loading: true,
      isSidebarOpen: getSidebarStateFromLocalStorage(),
      toggleSidbarMenu() {
        this.isSidebarOpen = !this.isSidebarOpen
        setSidebarStateToLocalStorage(this.isSidebarOpen)
      },
      isSettingsPanelOpen: false,
      isSearchBoxOpen: false,
  }
}

// Tooltip Script

document.querySelectorAll('[data-tooltip]').forEach(el => {
  const text = el.getAttribute('data-tooltip');
  const placement = el.getAttribute('data-tooltip-placement') || 'top';

  const tooltip = document.createElement('div');
  tooltip.className = `
    fixed z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-md 
    opacity-0 pointer-events-none transition-opacity duration-200 whitespace-nowrap
  `;
  tooltip.textContent = text;

  // Add arrow
  const arrow = document.createElement('div');
  arrow.className = 'tooltip-arrow';
  tooltip.appendChild(arrow);

  document.body.appendChild(tooltip); // Append outside clipping

  const showTooltip = () => {
    tooltip.style.opacity = 1;

    // Clear old arrow direction classes
    arrow.classList.remove('tooltip-arrow-top', 'tooltip-arrow-bottom', 'tooltip-arrow-left', 'tooltip-arrow-right');

    const rect = el.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();

    let top = 0, left = 0;

    switch (placement) {
      case 'top':
        top = rect.top - tooltipRect.height - 10;
        left = rect.left + rect.width / 2 - tooltipRect.width / 2;
        arrow.classList.add('tooltip-arrow-top');
        break;
      case 'bottom':
        top = rect.bottom + 10;
        left = rect.left + rect.width / 2 - tooltipRect.width / 2;
        arrow.classList.add('tooltip-arrow-bottom');
        break;
      case 'left':
        top = rect.top + rect.height / 2 - tooltipRect.height / 2;
        left = rect.left - tooltipRect.width - 10;
        arrow.classList.add('tooltip-arrow-left');
        break;
      case 'right':
        top = rect.top + rect.height / 2 - tooltipRect.height / 2;
        left = rect.right + 10;
        arrow.classList.add('tooltip-arrow-right');
        break;
    }

    tooltip.style.top = `${top}px`;
    tooltip.style.left = `${left}px`;
  };

  const hideTooltip = () => {
    tooltip.style.opacity = 0;
  };

  el.addEventListener('mouseenter', showTooltip);
  el.addEventListener('mouseleave', hideTooltip);
});
