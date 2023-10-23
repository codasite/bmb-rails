import { ReactComponent as SpinnerSVG } from '../assets/icons/spinner.svg'

export const Spinner = () => {
  return (
    // <div className='tw-animate-spin'>
    <div className="tw-animate-spin tw-h-60 tw-w-60">
      <SpinnerSVG />
    </div>
  )
}
