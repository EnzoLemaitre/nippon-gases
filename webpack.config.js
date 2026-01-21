const dev = true;
const path = require("path");
const webpack = require("webpack");
const TerserPlugin = require("terser-webpack-plugin");

const config = {
	mode: "development",
	watch: true,
	entry: {
		lastSolutions: "./assets/js/components/lastSolutions.js",
		supplyGases: "./assets/js/components/supplyGases.js",
		lastGases: "./assets/js/components/lastGases.js",
		slider: "./assets/js/components/slider.js",
		greenCompo: "./assets/js/components/greenCompo.js",
		greenFor: "./assets/js/components/greenFor.js",
		history: "./assets/js/components/history.js",
		distributor: "./assets/js/components/distributorSearch.js",
	},

	output: {
		path: path.resolve("./dist"),
		filename: dev ? "[name].js" : "[name].[chunkhash:8].js",
		publicPath: "/dist/",
	},

	resolve: {
		alias: {
			"@js": path.resolve("./assets/js/"),
			vue$: "vue/dist/vue.esm-bundler.js",
		},
	},

	optimization: {
		minimize: true,
		minimizer: [
			new TerserPlugin({
				terserOptions: {
					format: { comments: false },
				},

				extractComments: false,
			}),
		],
	},

	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /(node_modules|bower_components)/,
				use: {
					loader: "babel-loader",

					options: {
						presets: [["@babel/preset-env", { targets: "defaults" }]],
					},
				},
			},
			{
				test: /\.css$/,
				use: ["style-loader", "css-loader"],
			},
			{
				test: /\.m?js$/,
				enforce: "pre",
				use: ["source-map-loader"],
			},
		],
	},

	plugins: [
		new webpack.DefinePlugin({
			__VUE_OPTIONS_API__: JSON.stringify(true),
			__VUE_PROD_DEVTOOLS__: JSON.stringify(false),
			__VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false),
		}),
	],
};

module.exports = config;
