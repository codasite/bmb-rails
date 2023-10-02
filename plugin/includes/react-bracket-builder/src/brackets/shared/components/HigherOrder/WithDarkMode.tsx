import React, { useState } from 'react'
import { DarkModeContext } from '../../context'

export const WithDarkMode = (Component: React.ComponentType<any>) => {

	return (props: any) => {
		const [darkMode, setDarkMode] = React.useState(true)

		return (
			<DarkModeContext.Provider value={darkMode}>
				<Component
					{...props}
					darkMode={darkMode}
					setDarkMode={setDarkMode}
				/>
			</DarkModeContext.Provider>
		)
	}
}
