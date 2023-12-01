import { ReactComponent as SpinnerSVG } from '../../../assets/icons/spinner.svg'

interface SpinnerProps {
  fill?: string
  height?: number
  width?: number
}

export const Spinner = ({ height = 60, width = 60, fill = 'black' }) => {
  return (
    // <div className='tw-animate-spin'>
    <div className="tw-animate-spin" style={{ height: height, width: width }}>
      <SpinnerSVG fill={fill} />
    </div>
  )
}
