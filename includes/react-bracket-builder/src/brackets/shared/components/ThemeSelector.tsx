import React from 'react';

interface ThemeSelectorProps {
	darkMode?: boolean;
	setDarkMode?: (darkMode: boolean) => void;
}

export const ThemeSelector = (props: ThemeSelectorProps) => {
	const {
		darkMode,
		setDarkMode,
	} = props;
	return (
		<div className='tw-flex tw-items-center tw-justify-center tw-font-600 tw-gap-14'>
			<span className='tw-text-dd-blue dark:tw-text-white'>Theme</span>
			<button onClick={() => setDarkMode?.(!darkMode)} className='tw-flex tw-items-center tw-justify-end dark:tw-justify-start tw-w-[71px] tw-h-30 tw-px-2 tw-rounded-16 dark:tw-border-2 tw-border-solid tw-border-white tw-cursor-pointer tw-bg-dd-blue dark:tw-bg-none'>
				<div className='tw-w-[47px] tw-h-[22px] tw-rounded-16 tw-bg-white tw-text-10 tw-flex tw-items-center tw-justify-center'>
					<span className='tw-text-dd-blue tw-font-600 tw-text-sans tw-uppercase'>{darkMode ? 'dark' : 'light'}</span>
				</div>
			</button>
		</div>
	)
}