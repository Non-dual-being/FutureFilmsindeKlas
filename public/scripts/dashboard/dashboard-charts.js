const data = window.FUTURE_DASH;
if (data) {
  const visitorsCtx = document.getElementById('visitorsPerDayChart');
  if (visitorsCtx && data.dailyLabels && data.dailyLabels.length) {
    new Chart(visitorsCtx, {
            type: "line",
      data: {
        labels: data.dailyLabels,
        datasets: [
          {
            label: "New visitors",
            data: data.dailyValues,

            borderColor: "rgba(0, 175, 255, 0.9)",
            backgroundColor: "rgba(0, 175, 255, 0.12)",

            fill: true,
            borderWidth: 2,

            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: "rgba(255, 255, 255, 0.9)",
            pointBorderColor: "rgba(0, 175, 255, 0.9)",
            pointBorderWidth: 1,

            tension: 0.3
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 250 },

        layout: { padding: { top: 6, right: 8, bottom: 2, left: 2 } },

        scales: {
          x: {
            grid: { color: "rgba(255, 255, 255, 0.04)" },
            ticks: {
              color: "rgba(255, 255, 255, 0.65)",
              maxRotation: 0,
              autoSkip: true,
              maxTicksLimit: 7
            }
          },
          y: {
            beginAtZero: true,
            grid: { color: "rgba(255, 255, 255, 0.08)" },
            ticks: {
              color: "rgba(255, 255, 255, 0.65)",
              precision: 0 // integers
            }
          }
        },

        plugins: {
          legend: {
            labels: {
              color: "rgba(255, 255, 255, 0.85)",
              boxWidth: 18,
              boxHeight: 8
            }
          },
          tooltip: {
            backgroundColor: "rgba(20, 22, 26, 0.92)",
            borderColor: "rgba(255, 255, 255, 0.12)",
            borderWidth: 1,
            titleColor: "rgba(255, 255, 255, 0.9)",
            bodyColor: "rgba(255, 255, 255, 0.8)",
            callbacks: {
              // label: "New visitors: 12"
              label: (ctx) => `New visitors: ${ctx.parsed.y}`
            }
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



