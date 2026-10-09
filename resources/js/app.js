

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function drawLocalCharts() {
    document.querySelectorAll('canvas[data-local-chart]').forEach((canvas) => {
        const labels = JSON.parse(canvas.dataset.chartLabels || '[]');
        const datasets = JSON.parse(canvas.dataset.chartDatasets || '[]');
        const type = canvas.dataset.chartType;
        const showLegend = canvas.dataset.chartLegend === 'true';
        const numberFormat = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 });

        const draw = () => {
            const bounds = canvas.getBoundingClientRect();

            if (!bounds.width || !bounds.height) {
                return;
            }

            const pixelRatio = window.devicePixelRatio || 1;
            canvas.width = Math.round(bounds.width * pixelRatio);
            canvas.height = Math.round(bounds.height * pixelRatio);

            const context = canvas.getContext('2d');
            context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
            context.clearRect(0, 0, bounds.width, bounds.height);
            context.font = '12px Inter, ui-sans-serif, system-ui, sans-serif';
            context.textBaseline = 'middle';

            const left = 52;
            const right = 12;
            const top = showLegend ? 34 : 12;
            const bottom = 34;
            const plotWidth = Math.max(0, bounds.width - left - right);
            const plotHeight = Math.max(0, bounds.height - top - bottom);
            const values = datasets.flatMap((dataset) => dataset.values.map(Number));
            const maxValue = Math.max(0, ...values);
            const maxTick = Math.max(1, Math.ceil(maxValue / 4) * 4);

            context.strokeStyle = 'rgba(148, 163, 184, .18)';
            context.fillStyle = '#94a3b8';
            context.textAlign = 'right';

            for (let tick = 0; tick <= 4; tick += 1) {
                const y = top + (plotHeight * tick) / 4;
                const value = maxTick * (1 - tick / 4);

                context.beginPath();
                context.moveTo(left, y);
                context.lineTo(left + plotWidth, y);
                context.stroke();
                context.fillText(numberFormat.format(value), left - 8, y);
            }

            context.textAlign = 'center';
            const labelCount = Math.min(10, labels.length);

            for (let tick = 0; tick < labelCount; tick += 1) {
                const index = labelCount === 1
                    ? 0
                    : Math.round((tick * (labels.length - 1)) / (labelCount - 1));
                const x = left + (plotWidth * (index + 0.5)) / Math.max(1, labels.length);

                context.fillText(String(labels[index]).slice(0, 12), x, top + plotHeight + 19);
            }

            if (showLegend) {
                let legendX = left;
                context.textAlign = 'left';

                datasets.forEach((dataset) => {
                    context.fillStyle = dataset.color;
                    context.fillRect(legendX, 12, 8, 8);
                    context.fillStyle = '#64748b';
                    context.fillText(dataset.label, legendX + 13, 16);
                    legendX += context.measureText(dataset.label).width + 30;
                });
            }

            if (!labels.length || !datasets.length || !plotWidth || !plotHeight) {
                return;
            }

            if (type === 'bar') {
                const groupWidth = plotWidth / labels.length;
                const barWidth = Math.max(1, (groupWidth * 0.72) / datasets.length);

                datasets.forEach((dataset, datasetIndex) => {
                    context.fillStyle = dataset.color;

                    dataset.values.forEach((rawValue, index) => {
                        const value = Number(rawValue) || 0;
                        const height = (value / maxTick) * plotHeight;
                        const x = left + groupWidth * index + groupWidth / 2
                            + (datasetIndex - (datasets.length - 1) / 2) * barWidth
                            - barWidth / 2;

                        context.fillRect(x, top + plotHeight - height, barWidth, height);
                    });
                });

                return;
            }

            datasets.forEach((dataset) => {
                const points = dataset.values.map((rawValue, index) => ({
                    x: left + (plotWidth * (index + 0.5)) / labels.length,
                    y: top + plotHeight - ((Number(rawValue) || 0) / maxTick) * plotHeight,
                }));

                if (!points.length) {
                    return;
                }

                context.beginPath();
                context.moveTo(points[0].x, top + plotHeight);
                points.forEach((point) => context.lineTo(point.x, point.y));
                context.lineTo(points[points.length - 1].x, top + plotHeight);
                context.closePath();
                context.fillStyle = 'rgba(99, 102, 241, .12)';
                context.fill();

                context.beginPath();
                points.forEach((point, index) => {
                    if (index === 0) {
                        context.moveTo(point.x, point.y);
                    } else {
                        context.lineTo(point.x, point.y);
                    }
                });
                context.strokeStyle = dataset.color;
                context.lineWidth = 2.5;
                context.stroke();

                if (labels.length <= 30) {
                    context.fillStyle = dataset.color;
                    points.forEach((point) => {
                        context.beginPath();
                        context.arc(point.x, point.y, 3, 0, Math.PI * 2);
                        context.fill();
                    });
                }
            });
        };

        draw();

        if ('ResizeObserver' in window) {
            new ResizeObserver(draw).observe(canvas.parentElement);
        } else {
            window.addEventListener('resize', draw);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', drawLocalCharts, { once: true });
} else {
    drawLocalCharts();
}
