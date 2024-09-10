const ToggleSwitch = ({ isOn, handleToggle, onLabel, offLabel }) => {
  return (
    <button
      onClick={handleToggle}
      className={`tw-flex tw-items-center ${
        isOn ? 'tw-justify-start' : 'tw-justify-end'
      } tw-w-[71px] tw-h-30 tw-px-2 tw-rounded-16 tw-border-2 tw-border-solid tw-border-white tw-cursor-pointer tw-bg-dd-blue dark:tw-bg-none`}
    >
      <div className="tw-w-[47px] tw-h-[22px] tw-rounded-16 tw-bg-white tw-text-10 tw-flex tw-items-center tw-justify-center">
        <span className="tw-text-dd-blue tw-font-600 tw-text-sans tw-uppercase">
          {isOn ? onLabel : offLabel}
        </span>
      </div>
    </button>
  )
}

export default ToggleSwitch
