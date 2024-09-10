import { useContext } from 'react'
import { DarkModeContext } from '../context/context'
import ToggleSwitch from '../../../ui/ToggleSwitch'

export const ThemeSelector = () => {
  const { darkMode, setDarkMode } = useContext(DarkModeContext)

  return (
    <div className="tw-flex tw-items-center tw-justify-center tw-font-600 tw-gap-14">
      <span className="tw-text-dd-blue dark:tw-text-white">Theme</span>
      <ToggleSwitch
        isOn={darkMode}
        handleToggle={() => setDarkMode?.(!darkMode)}
        onLabel="dark"
        offLabel="light"
      />
    </div>
  )
}
