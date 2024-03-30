import { ReactComponent as SpinnerSVG } from '../../../assets/icons/spinner.svg'

export const Spinner = ({ height = 60, width = 60, fill = 'black' }) => {
  return (
    // <div className='tw-animate-spin'>
    <div className="tw-animate-spin" style={{ height: height, width: width }}>
      <SpinnerSVG fill={fill} />
    </div>
  )
}
