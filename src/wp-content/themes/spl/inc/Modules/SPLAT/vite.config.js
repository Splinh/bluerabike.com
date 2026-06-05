import * as path from 'path';
import { fileURLToPath } from 'url';
import { sharedConfig } from '../../../../../../../vite.config.shared.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const dir = path.resolve(__dirname).replace(/\\/g, '/');
const resources = `${dir}/resources`;
const assets = `${dir}/assets`;

export default {
	...sharedConfig,
	root: dir,
	build: {
		...sharedConfig.build,
		outDir: assets,
		assetsDir: '',
		emptyOutDir: true,
		rollupOptions: {
			input: {
				admin: `${resources}/scripts/admin.js`,
			},
			output: {
				entryFileNames: 'js/[name].[hash].js',
				chunkFileNames: 'js/[name].[hash].js',
				assetFileNames: (assetInfo) => {
					if (assetInfo.name && assetInfo.name.endsWith('.css')) {
						return 'css/[name].[hash].css';
					}
					return '[name].[hash].[ext]';
				},
			},
		},
	},
};
