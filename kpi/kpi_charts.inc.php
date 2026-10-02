<?php
/**
 * Grafik KPI (Chart.js) — myadm & myapp (kpi_user, kpi_results_user, kpi_validation)
 */
if (!function_exists('kpiChartsAssetBase')) {
	function kpiChartsAssetBase() {
		global $baseurl;
		if (!empty($baseurl) && is_string($baseurl)) {
			return rtrim($baseurl, '/');
		}
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$script = $_SERVER['SCRIPT_NAME'] ?? '';
		if (preg_match('#^(.*?)/(?:myadm|myapp)/#', $script, $m)) {
			return $scheme . '://' . $host . $m[1];
		}
		return $scheme . '://' . $host;
	}
}

if (!function_exists('kpiChartsPerspectivePair')) {
	/** Dua kurva: rating & weight per perspektif */
	function kpiChartsPerspectivePair($idPrefix, array $labels, array $ratings, array $weights) {
		if (empty($labels)) {
			return [];
		}
		$charts = [
			[
				'id' => $idPrefix . 'Rating',
				'title' => 'Kurva rating per perspektif',
				'type' => 'line',
				'labels' => $labels,
				'datasets' => [[
					'label' => 'Rating (1–5)',
					'data' => $ratings,
					'borderColor' => '#4f46e5',
					'backgroundColor' => 'rgba(79, 70, 229, 0.12)',
					'fill' => true,
					'tension' => 0.35,
					'pointRadius' => 5,
				]],
				'options' => [
					'responsive' => true,
					'maintainAspectRatio' => false,
					'scales' => ['y' => ['min' => 0, 'max' => 5, 'ticks' => ['stepSize' => 1]]],
				],
			],
		];
		if (!empty($weights)) {
			$charts[] = [
				'id' => $idPrefix . 'Weight',
				'title' => 'Kurva weight (%) per perspektif',
				'type' => 'line',
				'labels' => $labels,
				'datasets' => [[
					'label' => 'Weight (%)',
					'data' => $weights,
					'borderColor' => '#f59e0b',
					'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
					'fill' => true,
					'tension' => 0.35,
					'pointRadius' => 4,
				]],
				'options' => [
					'responsive' => true,
					'maintainAspectRatio' => false,
					'scales' => ['y' => ['min' => 0, 'max' => 100]],
				],
			];
		}
		return $charts;
	}
}

if (!function_exists('kpiChartsTotalPerUser')) {
	function kpiChartsTotalPerUser($id, $title, array $labels, array $totals) {
		if (empty($labels)) {
			return [];
		}
		return [[
			'id' => $id,
			'title' => $title,
			'type' => 'line',
			'labels' => $labels,
			'datasets' => [[
				'label' => 'Total Final Rating',
				'data' => $totals,
				'borderColor' => '#059669',
				'backgroundColor' => 'rgba(5, 150, 105, 0.15)',
				'fill' => true,
				'tension' => 0.35,
				'pointRadius' => 4,
			]],
			'options' => [
				'responsive' => true,
				'maintainAspectRatio' => false,
				'scales' => ['y' => ['min' => 0, 'max' => 5, 'ticks' => ['stepSize' => 1]]],
			],
		]];
	}
}

if (!function_exists('kpiChartsRenderBlock')) {
	/**
	 * @param array<int, array{id: string, title: string, labels: array, datasets: array}> $charts
	 */
	function kpiChartsRenderBlock(array $charts) {
		if (empty($charts)) {
			return;
		}
		$assetBase = kpiChartsAssetBase();
		$chartJsUrl = $assetBase . '/assets/chartjs/chart.js';
		$cdnFallback = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js';
		?>
		<style>
			.kpi-charts-wrap { margin-top: 20px; }
			.kpi-charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; }
			.kpi-chart-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; }
			.kpi-chart-panel h4 { margin: 0 0 10px; font-size: 14px; font-weight: 600; color: #374151; }
			.kpi-chart-panel .chart-holder { position: relative; height: 280px; width: 100%; }
		</style>
		<div class="kpi-charts-wrap">
			<div class="kpi-charts-grid">
				<?php foreach ($charts as $chart): ?>
				<div class="kpi-chart-panel">
					<h4><?= htmlspecialchars($chart['title'], ENT_QUOTES, 'UTF-8') ?></h4>
					<div class="chart-holder">
						<canvas id="<?= htmlspecialchars($chart['id'], ENT_QUOTES, 'UTF-8') ?>"></canvas>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<script>
		window.__kpiChartsQueue = window.__kpiChartsQueue || [];
		window.__kpiChartsQueue.push(<?= json_encode($charts, JSON_UNESCAPED_UNICODE) ?>);
		if (!window.__kpiChartsBootScheduled) {
			window.__kpiChartsBootScheduled = true;
			(function () {
				var localUrl = <?= json_encode($chartJsUrl) ?>;
				var cdnUrl = <?= json_encode($cdnFallback) ?>;
				function loadScript(url, cb) {
					var s = document.createElement('script');
					s.src = url;
					s.onload = function () { cb(); };
					s.onerror = function () { cb(new Error('load fail')); };
					document.head.appendChild(s);
				}
				function initKpiCharts() {
					if (typeof Chart === 'undefined') return;
					(window.__kpiChartsQueue || []).forEach(function (charts) {
						charts.forEach(function (cfg) {
							var el = document.getElementById(cfg.id);
							if (!el) return;
							if (el._kpiChart) el._kpiChart.destroy();
							el._kpiChart = new Chart(el, {
								type: cfg.type || 'line',
								data: { labels: cfg.labels || [], datasets: cfg.datasets || [] },
								options: cfg.options || { responsive: true, maintainAspectRatio: false }
							});
						});
					});
				}
				function boot() {
					if (typeof Chart !== 'undefined') {
						initKpiCharts();
						return;
					}
					loadScript(localUrl, function () {
						if (typeof Chart !== 'undefined') {
							initKpiCharts();
							return;
						}
						loadScript(cdnUrl, initKpiCharts);
					});
				}
				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', boot);
				} else {
					boot();
				}
			})();
		}
		</script>
		<?php
	}
}
