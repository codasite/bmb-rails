import { useState, useEffect } from 'react';

interface WindowDimensions {
	width: number;
	height: number;
}

function getWindowDimensions(): WindowDimensions {
	const { innerWidth: width, innerHeight: height } = window;
	return {
		width,
		height
	};
}

export function useWindowDimensions(): WindowDimensions {
	const [windowDimensions, setWindowDimensions] = useState<WindowDimensions>(
		getWindowDimensions()
	);

	useEffect(() => {
		function handleResize(): void {
			// const { width, height } = getWindowDimensions();
			// console.log('width: ', width);
			// console.log('height: ', height);

			setWindowDimensions(getWindowDimensions());
		}

		window.addEventListener('resize', handleResize);
		return (): void => window.removeEventListener('resize', handleResize);
	}, []);

	return windowDimensions;
}
