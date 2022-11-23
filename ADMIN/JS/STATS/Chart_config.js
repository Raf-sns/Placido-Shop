
/**
 *	Chart.js config
 * 	Thanks to the Chart.js Team ! See more: https://www.chartjs.org/
 * 	=> show ' Chart.defaults ' in console
 */
// Chart.defaults
// responsive / ratio - bug !
// Chart.defaults.responsive = true;
// Chart.defaults.maintainAspectRatio = false;

	// CHARTS CONSTANTS
	const Locations = ['cities','regions','countries','timezones'];
	const Locations_translate = [ $.o.tr.cities, $.o.tr.regions, $.o.tr.countries, $.o.tr.timezones ];

	const Stats_color_line = '#4be9b5';
	const BackgroundColorStack_1 = '#0fd798';
	const BackgroundColorStack_2 = '#ab46ed';
	const BackgroundColorStack_3 = '#00deff'; // light blue line
	const Fill_color = 'rgba(230, 230, 230, 0.3)'; // fill opacity
	const Fill_color_2 = 'rgba(80, 255, 197, 0.38)'; // green transparent

	// animation duration
	Chart.defaults.animation.duration = 1000;

	Chart.defaults.responsive = true;
	Chart.defaults.maintainAspectRatio = false;

	// LAYOUT Padding of chart
	Chart.defaults.layout.autoPadding = false; // or false ans set :
	Chart.defaults.layout.padding = {
		bottom: 20,
		left: 10,
		right: 20,
		top: 10
	};

	// colors fonts
	Chart.defaults.color = '#e6e6e6';
	Chart.defaults.font = {
		family: "'Open Sans', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
		lineHeight: 1.2,
		size: 18,
		style: "normal",
		weight: "normal"
	};

	// default by TYPES OF CHARTS -> Chart.defaults.elements
	// ARC > doughnut
	Chart.defaults.elements.arc.borderColor = '#2f4149'; //'rgba(62 78 86 / 72%)'; //'#3e4e56';
	Chart.defaults.elements.arc.backgroundColor = ARR_color;
	Chart.defaults.elements.arc.borderWidth = 1;

	// thickness of doughnut : '60%' - pie : 0
	Chart.defaults.datasets.doughnut.cutout = '60%';

	// BAR
	Chart.defaults.elements.bar.borderColor = ''; //'#2f4149';
	Chart.defaults.elements.bar.inflateAmount = 0;
	Chart.defaults.elements.bar.borderWidth = 0;
	Chart.defaults.elements.bar.backgroundColor = ARR_color; //'#3a3e42';
	Chart.defaults.elements.bar.maxBarThickness = 20;
	// Chart.defaults.elements.bar.barThickness = 10;
	// Chart.defaults.elements.bar.barThickness = 'flex';
	Chart.defaults.elements.bar.borderSkipped = true;


	// LINE
	// ingnored if cubicInterpolationMode = 'monotone'
	Chart.defaults.elements.line.tension = 0.2;
	// 'default' / 'monotone'->get exact values
	Chart.defaults.elements.line.cubicInterpolationMode = 'monotone';
	Chart.defaults.elements.line.fill = false;
	Chart.defaults.elements.line.backgroundColor = Stats_color_line;
	Chart.defaults.elements.line.borderColor = Stats_color_line;
	Chart.defaults.elements.line.borderCapStyle = 'round';
	Chart.defaults.elements.line.borderJoinStyle = 'round';
	Chart.defaults.elements.line.borderWidth = 2;
	// Si vrai, des lignes seront tracées entre les points sans données ou nulles
	Chart.defaults.elements.line.spanGaps = true;
	Chart.defaults.elements.line.stepped = false; // line in steps

	// POINT - element
	Chart.defaults.elements.point = {

		backgroundColor: Stats_color_line,
		borderColor: Stats_color_line,
		borderWidth: 1,
		hitRadius: 20, // distance to fire hover behaviour
		hoverBorderWidth: 2,
		hoverRadius: 10, // size on over
		radius: 3, // size of point
		pointStyle: "circle", // point style: 'star', 'triangle', ...
		rotation: 0,
	};

	// TITLES  is in .plugins namespace
	Chart.defaults.plugins.title = {
		display: false,
		align: 'center',
		color: '#e6e6e6',
		font: {
			weight: 'normal',
			size : 22
		},
		fullSize: true,
		padding: {
			top: 16,
			bottom: 16,
		},
		position: 'top',
		text: '',
		weight: 1000
	};


	// tooltips
	Chart.defaults.plugins.tooltip.padding = {
		bottom: 10,
		left: 10,
		right: 10,
		top: 10
	};
	Chart.defaults.plugins.tooltip.titleAlign = 'center';
	Chart.defaults.plugins.tooltip.titleFont = {
		size: 20,
		weight: 'bold', // title tooltip
	};
	Chart.defaults.plugins.tooltip.bodyAlign = 'center';
	Chart.defaults.plugins.tooltip.bodyFont = {
		size: 20,
		weight: 'bold', // text body tooltip
	};
	Chart.defaults.plugins.tooltip.displayColors = false;
	Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(58, 62, 66, 0.92)';
	Chart.defaults.plugins.tooltip.position = 'average';


	// LEGEND
	Chart.defaults.plugins.legend.position = 'bottom';
	Chart.defaults.plugins.legend.align = 'start'; // start / center
	Chart.defaults.plugins.legend.onClick = null; // not work - work in .OPTIONS
	Chart.defaults.plugins.legend.labels.boxWidth = 20;
	Chart.defaults.plugins.legend.labels.boxHeight = 15;
	Chart.defaults.plugins.legend.labels.padding = 20;
	Chart.defaults.plugins.legend.labels.usePointStyle = false; // true -> legends lebels as circles
	Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
	/**
	 *	Chart.js config
	 * 	Thanks to the Chart.js Team ! See more: https://www.chartjs.org/
	 */
