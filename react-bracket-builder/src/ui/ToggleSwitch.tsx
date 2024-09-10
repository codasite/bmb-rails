const ToggleSwitch = ({
  isOn,
  handleToggle,
  onLabel,
  offLabel,
  color,
}: {
  isOn: boolean
  handleToggle: () => void
  onLabel?: string
  offLabel?: string
  color?: 'white' | 'green'
}) => {
  const showCircle = !offLabel && !onLabel
  const getColorClass = () => {
    switch (color) {
      case 'green':
        return 'tw-border-green tw-text-green'
      default:
        return 'tw-border-white tw-text-white'
    }
  }

  return (
    <button
      onClick={handleToggle}
      className={`tw-flex tw-items-center ${
        !showCircle ? 'tw-w-[71px] tw-h-31' : 'tw-w-[30px]'
      } tw-p-[3px] tw-rounded-16 tw-border-2 tw-border-solid ${getColorClass()} tw-cursor-pointer tw-bg-dd-blue dark:tw-bg-none tw-transition-all tw-duration-300`}
    >
      {showCircle && (
        <svg
          className="tw-transition-transform tw-duration-300"
          style={{ transform: isOn ? 'translateX(0)' : 'translateX(100%)' }}
          width="10"
          height="10"
          viewBox="0 0 10 10"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <circle cx="5" cy="5" r="5" fill="currentcolor" />
        </svg>
      )}
      {!showCircle && (
        <div
          className={`tw-w-[47px] tw-h-[22px] tw-rounded-16 tw-bg-white tw-text-10 tw-flex tw-items-center tw-justify-center tw-transition-transform tw-duration-300 ${
            isOn ? 'tw-translate-x-0' : 'tw-translate-x-[14px]'
          }`}
        >
          <span className="tw-text-dd-blue tw-font-600 tw-text-sans tw-uppercase">
            {isOn ? onLabel : offLabel}
          </span>
        </div>
      )}
    </button>
  )
}

export default ToggleSwitch
