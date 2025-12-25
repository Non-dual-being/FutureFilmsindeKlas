const data = window.FUTURE_DASH;
if (data) {
  const visitorsCtx = document.getElementById('visitorsPerDayChart');
  if (visitorsCtx && data.dailyLabels && data.dailyLabels.length) {
    new Chart(visitorsCtx, {
      type: 'line',
      data: {
        labels: data.dailyLabels,
        datasets: [
          {
            label: 'New Visitors',
            data: data.dailyValues,
            borderColor: 'rgba(52, 152, 219, 0.9)',
            backgroundColor: 'rgba(52, 152, 219, 0.18)',
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: 'rgba(255, 255, 255, 0.9)',
            tension: 0.25
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            grid: { color: 'rgba(255, 255, 255, 0.03)' }
          },
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(255, 255, 255, 0.06)' }
          }
        },
        plugins: {
          legend: {
            labels: { color: '#f5f7fa' }
          }
        }
      }
    });
  }

  const deviceCtx = document.getElementById('deviceChart');
  if (deviceCtx && data.deviceLabels && data.deviceLabels.length) {
    new Chart(deviceCtx, {
      type: 'doughnut',
      data: {
        labels: data.deviceLabels,
        datasets: [
          {
            label: 'Device',
            data: data.deviceValues,
            backgroundColor: [
              'rgba(52, 152, 219, 0.85)',
              'rgba(46, 204, 113, 0.85)',
              'rgba(155, 89, 182, 0.85)',
              'rgba(241, 196, 15, 0.85)'
            ],
            borderColor: 'rgba(15, 27, 47, 1)',
            borderWidth: 2
          }
        ]
      },
      options: {
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: '#f5f7fa' }
          }
        }
      }
    });
  }

}



